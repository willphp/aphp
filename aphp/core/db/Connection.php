<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core\db;

use aphp\core\Config;
use Closure;
use Exception;
use PDO;
use aphp\core\DebugBar;
use aphp\core\Single;

class Connection
{
    use Single;

    protected array $config;
    public string $prefix;
    protected ?object $pdo = null; //pdo
    protected ?object $stmt = null; //PDOStatement
    protected array $bind = [];
    protected int $numRows = 0;
    protected bool $transResult = true;

    private function __construct($config = [])
    {
        $this->config = Config::init()->get('database.default', []);
        if (!empty($config) && $config != 'default') {
            if (!is_array($config)) {
                $config = Config::init()->get('database.' . $config, []);
            }
            $this->config = array_merge($this->config, $config);
        }
        $this->prefix = $this->config['table_prefix'];
        $this->connect();
    }

    private function connect(): void
    {
        if (isset($this->config['dsn'])) {
            $this->config['db_driver'] = explode(':', $this->config['dsn'])[0];
        } else {
            $this->config['dsn'] = $this->parseDsn();
        }
        $this->config['pdo_params'] ??= [];
        $this->pdo = new PDO($this->config['dsn'], $this->config['db_user'], strval($this->config['db_pass']), $this->config['pdo_params']);
        if ($this->config['db_driver'] == 'mysql') {
            $this->pdo->exec("SET sql_mode = ''");
        }
    }

    private function parseDsn(): string
    {
        $port = isset($this->config['db_port']) ? ';port=' . $this->config['db_port'] : '';
        $charset = isset($this->config['db_charset']) ? ';charset=' . $this->config['db_charset'] : '';
        return $this->config['db_driver'] . ':host=' . $this->config['db_host'] . $port . ';dbname=' . $this->config['db_name'] . $charset;
    }

    public function getConfig(string $name = '')
    {
        if (empty($name)) {
            return $this->config;
        }
        return $this->config[$name] ?? '';
    }

    public function getNumRows(): int
    {
        return $this->numRows;
    }

    public function getInsertId(?string $pk = null)
    {
        return $this->pdo->lastInsertId($pk);
    }

    private function bindValue(array $bind = []): void
    {
        foreach ($bind as $key => $val) {
            $param = is_numeric($key) ? $key + 1 : ':' . $key;
            if (is_array($val)) {
                if (PDO::PARAM_INT == $val[1] && '' === $val[0]) {
                    $val[0] = 0;
                }
                $this->stmt->bindValue($param, $val[0], $val[1]);
            } else {
                $this->stmt->bindValue($param, $val);
            }
        }
    }

    private function trace(string $sql, array $bind = []): void
    {
        if (!empty($bind)) {
            $sql = $this->getRealSql($sql, $bind);
        }
        DebugBar::init()->trace($sql, 'sql');
    }

    public function query(string $sql, array $bind = [], bool $getObj = false)
    {
        if (APP_TRACE) {
            $this->trace($sql, $bind);
        }
        if (!empty($bind)) {
            $this->bind = $bind;
        }
        if (!empty($this->stmt) && $this->stmt->queryString != $sql) {
            $this->stmt = null;
        }
        try {
            if (!$this->stmt) {
                $this->stmt = $this->pdo->prepare($sql);
            }
            if (!empty($bind)) {
                $this->bindValue($bind);
            }
            $this->stmt->execute();
            if ($getObj) {
                return $this->stmt;
            }
            $res = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->numRows = count($res);
            return $res ?: [];
        } catch (Exception $e) {
            throw new Exception($sql . json_encode($bind, JSON_UNESCAPED_UNICODE) . ';' . $e->getMessage());
        }
    }

    public function execute(string $sql, array $bind = [], bool $getInsertId = false)
    {
        if (APP_TRACE) {
            $this->trace($sql, $bind);
        }
        if (!empty($bind)) {
            $this->bind = $bind;
        }
        if (!empty($this->stmt) && $this->stmt->queryString != $sql) {
            $this->stmt = null;
        }
        try {
            if (!$this->stmt) {
                $this->stmt = $this->pdo->prepare($sql);
            }
            if (!empty($bind)) {
                $this->bindValue($bind);
            }
            $this->stmt->execute();
            if ($getInsertId) {
                return $this->pdo->lastInsertId();
            }
            $this->numRows = $this->stmt->rowCount();
            return $this->numRows;
        } catch (Exception $e) {
            throw new Exception($sql . json_encode($bind, JSON_UNESCAPED_UNICODE) . ';' . $e->getMessage());
        }
    }

    public function getRealSql(string $sql, array $bind = []): string
    {
        if (empty($bind)) {
            return $sql;
        }
        $key = array_map(fn($v) => is_string($v) ? '/:' . $v . '/' : '/[?]/', array_keys($bind));
        $val = array_map(fn($v) => $this->quoteValue($v), $bind);
        return preg_replace($key, $val, $sql, 1);
    }

    private function quoteValue($value): string
    {
        if (is_array($value)) {
            [$value, $type] = $value;
        } else {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        }
        return ($type == PDO::PARAM_INT) ? strval(intval($value)) : $this->quote(strval($value));
    }

    public function quote(string $value): string
    {
        return $this->pdo->quote($value);
    }

    public function trans(Closure $closure): bool
    {
        try {
            $this->pdo->beginTransaction();
            call_user_func($closure);
            $this->pdo->commit();
        } catch (Exception $e) {
            $this->transResult = false;
            $this->pdo->rollback();
        }
        return $this->transResult;
    }

    public function startTrans(): Connection
    {
        $this->pdo->beginTransaction();
        return $this;
    }

    public function commit(): Connection
    {
        $this->pdo->commit();
        return $this;
    }

    public function rollback(): Connection
    {
        $this->pdo->rollback();
        return $this;
    }

    public function __sleep()
    {
        return ['prefix'];
    }

    public function __destruct()
    {
        $this->stmt = null;
        $this->pdo = null;
    }
}