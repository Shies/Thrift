CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `username` char(64) NOT NULL COMMENT '用户名',
  `nickname` varchar(64) NOT NULL DEFAULT '' COMMENT '昵称',
  `password` char(64) NOT NULL DEFAULT '' COMMENT '密码',
  `email` varchar(64) NOT NULL DEFAULT '' COMMENT '邮箱',
  `telephone` char(15) NOT NULL DEFAULT '' COMMENT '手机号码',
  `ctime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `mtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '用户表';

CREATE TABLE `auth_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(64) NOT NULL COMMENT '权限、角色名称',
  `type` tinyint(4) NOT NULL COMMENT '区分权限和角色',
  `description` text,
  `rule_id` int(11) DEFAULT NULL COMMENT '如果存在规则，关联到规则表',
  `data` text,
  `ctime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `mtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`type`),
  KEY `rule_id` (`rule_id`),
  KEY `type` (`type`),
  CONSTRAINT `auth_item_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `auth_rule` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='储存权限和角色的表';

CREATE TABLE `auth_item_child` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `parent` int(11) NOT NULL COMMENT 'auth_item id',
  `child` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  KEY `child` (`child`),
  CONSTRAINT `auth_item_child_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `auth_item` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `auth_item_child_ibfk_2` FOREIGN KEY (`child`) REFERENCES `auth_item` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='角色和权限(或角色和角色)的关联表';

CREATE TABLE `auth_assignment` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `item_id` int(11) NOT NULL COMMENT '权限或角色 auth_item ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `ctime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `auth_assignment_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `auth_item` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='角色（或权限）和用户的关联表';

CREATE TABLE `auth_rule` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(64) NOT NULL COMMENT '规则',
  `data` text,
  `ctime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `mtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='存储规则的表。规则是给角色和权限添加额外的约束，在代码层面实现具体规则';