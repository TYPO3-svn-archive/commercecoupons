#
# Table structure for table 'tx_commercecoupons_coupons'
#
CREATE TABLE tx_commercecoupons_coupons (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	code varchar(100) DEFAULT '' NOT NULL,
	dedication text NOT NULL,
	amount_net int(11) DEFAULT '0' NOT NULL,
	amount_gross int(11) DEFAULT '0' NOT NULL,
	amount_percent int(11) DEFAULT '0' NOT NULL,
	has_articles tinyint(4)  DEFAULT '0' NOT NULL,
	article blob NOT NULL,
	count int(11) DEFAULT '0' NOT NULL,
	type varchar(7) DEFAULT '' NOT NULL,
	newpid int(11) DEFAULT '0' NOT NULL,
	limit_start int(11) DEFAULT '0' NOT NULL,
	limit_end int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);

#
# Table structure for table 'tx_commercecoupons_articles'
#
CREATE TABLE tx_commercecoupons_articles (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    coupon_id blob NOT NULL,
    article_id blob NOT NULL,
    price_gross int(11) DEFAULT '0' NOT NULL,
    price_net int(11) DEFAULT '0' NOT NULL,
    amount int(11) DEFAULT '1' NOT NULL, 
				        
    PRIMARY KEY (uid), 
    KEY parent (pid)

);

CREATE TABLE tx_commerce_baskets (
	tx_commercecoupons_addedbycouponid blob NOT NULL,
	tx_commercecoupons_relatedcoupon blob NOT NULL,
);

#
# Table structure for table 'tx_commercecoupons_cashed'
#
CREATE TABLE tx_commercecoupons_cashed (
                uid int(11) NOT NULL auto_increment,
                pid int(11) DEFAULT '0' NOT NULL,
                tstamp int(11) DEFAULT '0' NOT NULL,
                crdate int(11) DEFAULT '0' NOT NULL,
                cruser_id int(11) DEFAULT '0' NOT NULL,
                fe_group int(11) DEFAULT '0' NOT NULL,
                fe_user int(11) DEFAULT '0' NOT NULL,
                deleted tinyint(4) DEFAULT '0' NOT NULL,
                hidden tinyint(4) DEFAULT '0' NOT NULL,
                coupon_pid int(11) DEFAULT '0' NOT NULL,
                sess_id varchar(11) DEFAULT '0' NOT NULL,
                order_pid int(11) DEFAULT '0' NOT NULL,

                PRIMARY KEY (uid),
                KEY parent (pid)
);
