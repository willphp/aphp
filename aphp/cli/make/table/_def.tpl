CREATE TABLE `{{$table_prefix}}{{$table_name}}` (
{{$table_fields|default='`id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT "ID",`status` tinyint(1) unsigned NOT NULL DEFAULT "0" COMMENT "状态",PRIMARY KEY (`id`)'}}
) ENGINE={{$table_engine}} DEFAULT CHARSET={{$table_charset}} COLLATE {{$table_collate}} COMMENT='{{$table_comment}}';