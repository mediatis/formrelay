CREATE TABLE tx_formrelay_domain_model_queue_job (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,

  label text DEFAULT '',
  hash text DEFAULT '',
  route text DEFAULT '',
  pass text DEFAULT '',

  status int(11) unsigned DEFAULT 0,
  skipped tinyint(4) unsigned DEFAULT '0' NOT NULL,
  status_message text DEFAULT '',
  serialized_data mediumtext DEFAULT '',

  changed int(11) unsigned DEFAULT '0' NOT NULL,
  created int(11) unsigned DEFAULT '0' NOT NULL,

  t3_origuid int(11) DEFAULT '0' NOT NULL,

  PRIMARY KEY (uid),
  KEY parent (pid)
);
