<?php
/*----------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: 113344.com
 |----------------------------------------------------------------
 | Author: 无念 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2023, 113344.com. All Rights Reserved.
 |---------------------------------------------------------------*/
declare(strict_types=1);

namespace willphp\core\db;

use Closure;
use Exception;
use PDO;
use willphp\core\Config;
use willphp\core\Single;

class Connection
{
    use Single;

    private array $config;
    protected ?object $pdo = null;
    protected ?object $sth = null;
    protected array $bind = [];
    protected string $sql = '';
    protected int $numRows = 0;

    private function __construct($config = [])
    {
        if (is_string($config) && $config != 'default') {
            $config = Config::init()->get('database.' . $config, []);
        }
        $this->config = array_merge(Config::init()->get('database.default', []), $config);
        $this->connect();
    }

    public function __destruct()
    {
        $this->sth = null;
        $this->pdo = null;
    }

    public function __sleep()
    {
        return ['sql'];
    }

    private function isMysql(): bool
    {
        return in_array($this->config['db_type'], ['pdo', 'mysql', 'mysqli']);
    }

    private function connect(): void
    {
        $this->config['dsn'] ??= $this->getDsn();
        $this->config['pdo_params'] ??= [];
        $this->pdo = new PDO($this->config['dsn'], $this->config['db_user'], strval($this->config['db_pwd']), $this->config['pdo_params']);
        if ($this->isMysql()) $this->pdo->exec("SET sql_mode = ''");
    }

    private function getDsn(): string
    {
        if (!$this->isMysql()) {
            return '';
        }
        $dsn = 'mysql:host=' . $this->config['db_host'] . ';dbname=' . $this->config['db_name'];
        if (isset($this->config['db_port'])) {
            $dsn .= ';port=' . $this->config['db_port'];
        }
        if (isset($this->config['db_charset'])) {
            $dsn .= ';charset=' . $this->config['db_charset'];
        }
        return $dsn;
    }

    public function getConfig(string $name = '')
    {
        if (empty($name)) {
            return $this->config;
        }
        return $this->config[$name] ?? '';
    }

    public function execute(string $sql, array $bind = []): int
    {
        return $this->exeSth($sql, $bind);
    }

    public function query(string $sql, array $bind = [], bool $getPdo = false)
    {
        return $this->exeSth($sql, $bind, true, $getPdo);
    }

    private function exeSth(string $sql, array $bind = [], bool $isQuery = false, bool $getPdo = false)
    {
        if (!empty($bind)) {
            $this->bind = $bind;
        }
        if (!empty($this->sth) && $this->sth->queryString != $sql) {
            $this->sth = null;
        }
        try {
            if (empty($this->sth)) {
                $this->sth = $this->pdo->prepare($sql);
            }
            $this->bindValue($bind);
            $this->sth->execute();
            if (!$isQuery) {
                $this->numRows = $this->sth->rowCount();
                return $this->numRows;
            }
            if ($getPdo) {
                return $this->sth;
            }
            $result = $this->sth->fetchAll(PDO::FETCH_ASSOC);
            $this->numRows = count($result);
            return $result ?: [];
        } catch (Exception $e) {
            throw new Exception($sql . json_encode($bind, JSON_UNESCAPED_UNICODE) . ';' . $e->getMessage());
        }
    }

    private function bindValue(array $bind = []): void
    {
        foreach ($bind as $key => $val) {
            $param = is_numeric($key) ? $key + 1 : ':' . $key;
            if (is_array($val)) {
                if (PDO::PARAM_INT == $val[1] && '' === $val[0]) {
                    $val[0] = 0;
                }
                $this->sth->bindValue($param, $val[0], $val[1]);
            } else {
                $this->sth->bindValue($param, $val);
            }
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

    public function getNumRows(): int
    {
        return $this->numRows;
    }

    public function getInsertId(string $pk = null)
    {
        return $this->pdo->lastInsertId($pk);
    }

    public function getLastSql(): string
    {
        return $this->getRealSql($this->sql, $this->bind);
    }

    public function startTrans(): Connection
    {
        $this->pdo->beginTransaction();
        return $this;
    }

    public function rollback(): Connection
    {
        $this->pdo->rollback();
        return $this;
    }

    public function commit(): Connection
    {
        $this->pdo->commit();
        return $this;
    }

    public function trans(Closure $closure): Connection
    {
        try {
            $this->startTrans();
            call_user_func($closure);
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
        }
        return $this;
    }
}