# -----------------------------------------------------------------------------
# Domain tables for EXT:mens_circle
# Note: No custom tt_content columns are required. Content elements use core
# fields and FlexForms only.
# -----------------------------------------------------------------------------

CREATE TABLE tx_menscircle_domain_model_event (
    uid int(11) unsigned NOT NULL auto_increment,
    pid int(11) unsigned DEFAULT '0' NOT NULL,
    tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    crdate int(11) unsigned DEFAULT '0' NOT NULL,
    cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
    deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
    hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
    starttime int(11) unsigned DEFAULT '0' NOT NULL,
    endtime int(11) unsigned DEFAULT '0' NOT NULL,
    sorting int(11) DEFAULT '0' NOT NULL,

    sys_language_uid int(11) DEFAULT '0' NOT NULL,
    l10n_parent int(11) unsigned DEFAULT '0' NOT NULL,
    l10n_diffsource mediumblob,

    title varchar(255) DEFAULT '' NOT NULL,
    slug varchar(2048) DEFAULT '' NOT NULL,
    teaser text,
    description text,
    event_date datetime DEFAULT NULL,
    start_time time DEFAULT NULL,
    end_time time DEFAULT NULL,
    location varchar(255) DEFAULT '' NOT NULL,
    street varchar(255) DEFAULT '' NOT NULL,
    postal_code varchar(32) DEFAULT '' NOT NULL,
    city varchar(255) DEFAULT '' NOT NULL,
    location_details text,
    max_participants int(11) unsigned DEFAULT '20' NOT NULL,
    cost_basis varchar(255) DEFAULT '' NOT NULL,
    is_published tinyint(1) unsigned DEFAULT '0' NOT NULL,
    image int(11) unsigned DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY language (l10n_parent, sys_language_uid),
    KEY idx_slug (slug),
    KEY idx_event_date (event_date),
    KEY idx_visibility (is_published, hidden, deleted)
);

CREATE TABLE tx_menscircle_domain_model_participant (
    uid int(11) unsigned NOT NULL auto_increment,
    pid int(11) unsigned DEFAULT '0' NOT NULL,
    tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    crdate int(11) unsigned DEFAULT '0' NOT NULL,
    cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
    deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
    hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

    first_name varchar(255) DEFAULT '' NOT NULL,
    last_name varchar(255) DEFAULT '' NOT NULL,
    email varchar(255) DEFAULT '' NOT NULL,
    phone varchar(64) DEFAULT '' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    UNIQUE KEY uniq_email (email)
);

CREATE TABLE tx_menscircle_domain_model_registration (
    uid int(11) unsigned NOT NULL auto_increment,
    pid int(11) unsigned DEFAULT '0' NOT NULL,
    tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    crdate int(11) unsigned DEFAULT '0' NOT NULL,
    cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
    deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
    hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

    event int(11) unsigned DEFAULT '0' NOT NULL,
    participant int(11) unsigned DEFAULT '0' NOT NULL,
    status varchar(32) DEFAULT 'registered' NOT NULL,
    registered_at datetime DEFAULT NULL,
    cancelled_at datetime DEFAULT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY idx_event (event),
    KEY idx_participant (participant),
    KEY idx_status (status),
    UNIQUE KEY uniq_event_participant (event, participant)
);

CREATE TABLE tx_menscircle_domain_model_newslettersubscription (
    uid int(11) unsigned NOT NULL auto_increment,
    pid int(11) unsigned DEFAULT '0' NOT NULL,
    tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    crdate int(11) unsigned DEFAULT '0' NOT NULL,
    cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
    deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
    hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

    participant int(11) unsigned DEFAULT '0' NOT NULL,
    token varchar(128) DEFAULT '' NOT NULL,
    subscribed_at datetime DEFAULT NULL,
    confirmed_at datetime DEFAULT NULL,
    unsubscribed_at datetime DEFAULT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY idx_participant (participant),
    UNIQUE KEY uniq_token (token),
    UNIQUE KEY uniq_participant (participant)
);

CREATE TABLE tx_menscircle_domain_model_testimonial (
    uid int(11) unsigned NOT NULL auto_increment,
    pid int(11) unsigned DEFAULT '0' NOT NULL,
    tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    crdate int(11) unsigned DEFAULT '0' NOT NULL,
    cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
    deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
    hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

    quote text,
    author_name varchar(255) DEFAULT '' NOT NULL,
    email varchar(255) DEFAULT '' NOT NULL,
    role varchar(255) DEFAULT '' NOT NULL,
    is_published tinyint(1) unsigned DEFAULT '0' NOT NULL,
    published_at datetime DEFAULT NULL,
    sort_order int(11) unsigned DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY idx_published (is_published, hidden, deleted),
    KEY idx_sort (sort_order)
);
