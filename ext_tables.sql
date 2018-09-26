CREATE TABLE tx_formz_domain_model_formmetadata (
    uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,

    hash varchar(64) DEFAULT NULL,
    class_name varchar(256) DEFAULT NULL,
    identifier varchar(256) DEFAULT NULL,
    metadata longblob NOT NULL,

    PRIMARY KEY (uid),
    INDEX i_hash (hash),
    INDEX i_class_name_identifier (class_name, identifier),
    UNIQUE KEY hash (hash)
);
