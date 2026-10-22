CREATE SCHEMA pramuksankalp;

CREATE TABLE pramuksankalp.discount_rule ( 
	discount_id          int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	scope                enum  NOT NULL    ,
	discount_type        enum  NOT NULL    ,
	discount_value       decimal(10,2)  NOT NULL    ,
	applies_to           enum  NOT NULL DEFAULT ALL   ,
	valid_from           date  NOT NULL    ,
	valid_to             date      ,
	active               tinyint  NOT NULL DEFAULT 1   ,
	created_at           date  NOT NULL DEFAULT CURRENT_DATE   ,
	updated_at           date      ,
	school_id            int      ,
	student_id           int      ,
	CONSTRAINT unq_discount_rule_school_id UNIQUE ( school_id ) ,
	CONSTRAINT unq_discount_rule_student_id UNIQUE ( student_id ) 
 ) engine=InnoDB;

ALTER TABLE pramuksankalp.discount_rule MODIFY scope enum  NOT NULL   COMMENT '''SCHOOL'',\n''STUDENT''';

ALTER TABLE pramuksankalp.discount_rule MODIFY discount_type enum  NOT NULL   COMMENT '''FLAT'',\n''PERCENT''';

ALTER TABLE pramuksankalp.discount_rule MODIFY applies_to enum  NOT NULL DEFAULT ALL  COMMENT 'ALL\nTUITION\netc..';

CREATE TABLE pramuksankalp.document ( 
	doc_id               int      ,
	student_id           int  NOT NULL    ,
	photo                blob      ,
	result               blob      ,
	achievement          longblob      ,
	satsang_verification blob      ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      ,
	CONSTRAINT unq_document_student_id UNIQUE ( student_id ) 
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.guardian_lang ( 
	gl_id                int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	lg_id                int  NOT NULL    ,
	guardian_id          int  NOT NULL    ,
	understand_lg        bit  NOT NULL DEFAULT 0   ,
	speak_lg             bit  NOT NULL DEFAULT 0   ,
	read_lg              bit  NOT NULL DEFAULT 0   ,
	write_lg             bit  NOT NULL DEFAULT 0   ,
	isprimary            bit  NOT NULL DEFAULT 0   ,
	notes                varchar(200)      ,
	CONSTRAINT unq_guardian_lang_lg_id UNIQUE ( lg_id ) ,
	CONSTRAINT unq_guardian_lang_guardian_id UNIQUE ( guardian_id ) 
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.`invoice_item ` ( 
	invoice_item_id      bigint  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	description          varchar(255)      ,
	qty                  decimal(10,2)      ,
	unit_amount          decimal(12,2)      ,
	line_amount          decimal(12,2)      ,
	invoice_id           bigint  NOT NULL    ,
	`fee_plan_id `       int      ,
	CONSTRAINT `unq_invoice_item _invoice_id` UNIQUE ( invoice_id ) ,
	CONSTRAINT `unq_invoice_item _fee_plan_id ` UNIQUE ( `fee_plan_id ` ) 
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.language ( 
	lg_id                int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	languagecode         varchar(20)  NOT NULL    ,
	languagename         varchar(100)  NOT NULL    
 ) engine=InnoDB;

ALTER TABLE pramuksankalp.language MODIFY languagecode varchar(20)  NOT NULL   COMMENT 'e.g., \n''GU'',\n''HI'', \n''EN''';

CREATE TABLE pramuksankalp.lg_commissionbatch ( 
	lgcbatch_id          int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            datetime      ,
	oldvalue             decimal(6,2)      ,
	newvalue             decimal(6,2)      ,
	user_id              varchar(255)      ,
	current              decimal(6,2)      ,
	CONSTRAINT unq_lg_commissionbatch_current UNIQUE ( current ) 
 );

CREATE TABLE pramuksankalp.lg_nterview ( 
	lginterview_id       int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	oldstatus            tinyint      ,
	newstatus            tinyint      ,
	reason               varchar(255)      ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	createdby            varchar(50)      ,
	interview_id         int  NOT NULL    ,
	CONSTRAINT unq_lg_nterview_interview_id UNIQUE ( interview_id ) 
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.lg_sys ( 
 );

CREATE TABLE pramuksankalp.lg_sysuser ( 
	lg_sysuser_id        int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	sysuser_id           int  NOT NULL    ,
	oldpasswd            varchar(250)      ,
	update_at            date      ,
	CONSTRAINT unq_lg_sysuser_sysuser_id UNIQUE ( sysuser_id ) 
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.parcel ( 
	parcel_id            int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	student_id           int  NOT NULL    ,
	parcelcom_id         int  NOT NULL    ,
	trakingnumber        varchar(150)  NOT NULL    ,
	notes                varchar(500)      ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	CONSTRAINT unq_parcel_student_id UNIQUE ( student_id ) ,
	CONSTRAINT unq_parcel_parcelcom_id UNIQUE ( parcelcom_id ) 
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.parcelcompany ( 
	parcelcom_id         int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	companyname          varchar(255)      ,
	trackingurl          varchar(500)      ,
	apiendpoint          varchar(255)      ,
	apikey               varchar(255)      ,
	supportcontact       varchar(50)      ,
	deliverytimeestimate varchar(50)      ,
	inactive             bit  NOT NULL DEFAULT 0   ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      
 ) engine=InnoDB;

ALTER TABLE pramuksankalp.parcelcompany MODIFY trackingurl varchar(500)     COMMENT 'URL format for tracking shipments (e.g., https://track.bludart.com/?id={tracking_number}).';

ALTER TABLE pramuksankalp.parcelcompany MODIFY apiendpoint varchar(255)     COMMENT 'Base URL if integrated via API';

ALTER TABLE pramuksankalp.parcelcompany MODIFY deliverytimeestimate varchar(50)     COMMENT 'Typical delivery time (e.g., "2-5 days").';

CREATE TABLE pramuksankalp.paymentlog ( 
	payment_id           int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	paymode_id           int  NOT NULL    ,
	processdatetime      datetime  NOT NULL    ,
	amount               decimal(10,2)      ,
	status               char(1)      ,
	txnid                varchar(100)  NOT NULL    ,
	orgresponse          longtext      ,
	discount             decimal(8,2)      ,
	invoice_id           bigint  NOT NULL    ,
	CONSTRAINT unq_paymentlog_paymode_id UNIQUE ( paymode_id ) ,
	CONSTRAINT unq_paymentlog_invoice_id UNIQUE ( invoice_id ) 
 ) engine=InnoDB;

ALTER TABLE pramuksankalp.paymentlog MODIFY status char(1)     COMMENT '0 - PENDING\n1 - AUTHORIZED\n2 - REFUNDED\n3 - FAILED\n4 - CANCELLED';

ALTER TABLE pramuksankalp.paymentlog MODIFY txnid varchar(100)  NOT NULL   COMMENT 'transction refrence id';

ALTER TABLE pramuksankalp.paymentlog MODIFY orgresponse longtext     COMMENT 'store whole response';

CREATE TABLE pramuksankalp.paymentmode ( 
	paymode_id           int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	paymodename          varchar(100)      ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      
 ) engine=InnoDB;

ALTER TABLE pramuksankalp.paymentmode MODIFY paymodename varchar(100)     COMMENT 'ex.\ncash\ncredit\ndebit\ncheck';

CREATE TABLE pramuksankalp.rolepermission ( 
	rolepermission_id    int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	permission_id        int  NOT NULL    ,
	role_id              int  NOT NULL    ,
	CONSTRAINT unq_rolepermission_permission_id UNIQUE ( permission_id ) ,
	CONSTRAINT unq_rolepermission_role_id UNIQUE ( role_id ) 
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.student_lang ( 
	stlg_id              int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	student_id           int  NOT NULL    ,
	lg_id                int  NOT NULL    ,
	understand_lg        bit  NOT NULL DEFAULT 0   ,
	speak_lg             bit  NOT NULL DEFAULT 0   ,
	read_lg              bit  NOT NULL DEFAULT 0   ,
	write_lg             bit  NOT NULL DEFAULT 0   ,
	isprimary            bit  NOT NULL DEFAULT 0   ,
	notes                varchar(200)      ,
	CONSTRAINT unq_guardian_lang_lg_id_0 UNIQUE ( lg_id ) ,
	CONSTRAINT unq_student_lang_student_id UNIQUE ( student_id ) 
 );

CREATE TABLE pramuksankalp.student_sembatches ( 
	std_batch_id         int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	student_id           int  NOT NULL    ,
	sembatches_id        smallint  NOT NULL    ,
	status               smallint  NOT NULL    ,
	assigned_at          timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP   ,
	completed_at         timestamp      ,
	CONSTRAINT unq_student_sembatches_student_id UNIQUE ( student_id ) ,
	CONSTRAINT unq_student_sembatches_sembatches UNIQUE ( sembatches_id ) 
 ) engine=InnoDB;

ALTER TABLE pramuksankalp.student_sembatches MODIFY status smallint  NOT NULL   COMMENT '0 -active\n1-completed\n2-dropped\n3-on-hold';

CREATE TABLE pramuksankalp.syssetup ( 
	syssetup_id          int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	setupvariable        varchar(100)  NOT NULL    ,
	setupvaluestr        varchar(500)      ,
	setupvaluenum        decimal(18,4)      ,
	setupvaluedate       timestamp      ,
	setupvaluejson       json      ,
	setupvartype         smallint   DEFAULT 0   
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.usercommission ( 
	usercmmission_id     int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	school_id            int  NOT NULL    ,
	cbatch_id            int  NOT NULL    ,
	sysuser_id           int  NOT NULL    ,
	valid_from           date  NOT NULL    ,
	valid_to             date      ,
	CONSTRAINT unq_usercommission_school_id UNIQUE ( school_id ) ,
	CONSTRAINT unq_usercommission_cbatch_id UNIQUE ( cbatch_id ) ,
	CONSTRAINT unq_usercommission_systemuser_id UNIQUE ( sysuser_id ) 
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.userschool ( 
	userschool_id        int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	school_id            int  NOT NULL    ,
	sysuser_id           int  NOT NULL    ,
	CONSTRAINT unq_userschool_school_id UNIQUE ( school_id ) ,
	CONSTRAINT unq_userschool_systemuser_id UNIQUE ( sysuser_id ) 
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.userstudent ( 
	userstudent_id       int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	sysuser_id           int  NOT NULL    ,
	student_id           int  NOT NULL    ,
	CONSTRAINT unq_userstudent_sysuser_id UNIQUE ( sysuser_id ) ,
	CONSTRAINT unq_userstudent_student_id UNIQUE ( student_id ) 
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.commissionbatch ( 
	cbatch_id            int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	ctype                smallint   DEFAULT 0   ,
	cvalue               decimal(6,2)      ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      
 ) engine=InnoDB;

ALTER TABLE pramuksankalp.commissionbatch MODIFY ctype smallint   DEFAULT 0  COMMENT '0 - persantage\n1 - flat Amount';

CREATE TABLE pramuksankalp.`fee_plan ` ( 
	`fee_plan_id `       int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	fee_name             varchar(200)  NOT NULL    ,
	amount               decimal(10,2)  NOT NULL    ,
	valid_from           date  NOT NULL    ,
	valid_to             date      ,
	created_at           date  NOT NULL DEFAULT CURRENT_DATE   ,
	updated_at           date   DEFAULT ON UPDATE CURRENT_TIMESTAMP   ,
	sembatches_id        smallint      ,
	school_id            int      ,
	std_id               int      ,
	CONSTRAINT `unq_fee_plan _created_at` UNIQUE ( created_at ) ,
	CONSTRAINT `unq_fee_plan _sembatches_id` UNIQUE ( sembatches_id ) ,
	CONSTRAINT `unq_fee_plan _valid_from` UNIQUE ( valid_from ) ,
	CONSTRAINT `unq_fee_plan _school_id` UNIQUE ( school_id ) ,
	CONSTRAINT `unq_fee_plan _std_id` UNIQUE ( std_id ) ,
	CONSTRAINT unq_fee_scope UNIQUE ( school_id, std_id, sembatches_id, fee_name, valid_from ) 
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.guardian ( 
	guardian_id          int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	student_id           int  NOT NULL    ,
	relation_id          int  NOT NULL    ,
	fname                varchar(100)  NOT NULL    ,
	lname                varchar(100)  NOT NULL    ,
	mname                varchar(100)  NOT NULL    ,
	gender               varchar(10)      ,
	phone                varchar(10)      ,
	email                varchar(255)      ,
	education            text      ,
	work_experience      varchar(255)      ,
	how_to_help          text      ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      ,
	createdby            varchar(100)      ,
	CONSTRAINT unq_guardian_student_id UNIQUE ( student_id ) ,
	CONSTRAINT unq_guardian_relation_id UNIQUE ( relation_id ) 
 ) engine=InnoDB;

ALTER TABLE pramuksankalp.guardian MODIFY gender varchar(10)     COMMENT 'M - Male\nF - Female\nO - Other';

CREATE TABLE pramuksankalp.interview ( 
	interview_id         int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	startat              date      ,
	endat                date      ,
	status               smallint      ,
	mode                 smallint      ,
	location             varchar(100)      ,
	roundnumber          tinyint      ,
	notes                text      ,
	createdat            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      ,
	student_id           int  NOT NULL    ,
	sysuser_id           int  NOT NULL    ,
	CONSTRAINT unq_lg_intervie_student_id UNIQUE ( student_id ) ,
	CONSTRAINT unq_lg_intervie_sysuser_id UNIQUE ( sysuser_id ) 
 ) engine=InnoDB;

ALTER TABLE pramuksankalp.interview MODIFY status smallint     COMMENT '1- SCHEDULED\n2- RESCHEDULED\n3- IN_PROGRESS\n4- COMPLETED\n5- CANCELLED';

ALTER TABLE pramuksankalp.interview MODIFY mode smallint     COMMENT '0- In-Person\n1 - Online\n2 - Phone';

ALTER TABLE pramuksankalp.interview MODIFY location varchar(100)     COMMENT 'room\nmeet link';

ALTER TABLE pramuksankalp.interview MODIFY roundnumber tinyint     COMMENT '1,2,3...';

CREATE TABLE pramuksankalp.invoice ( 
	invoice_id           bigint  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	invoice_date         date  NOT NULL DEFAULT CURRENT_DATE   ,
	due_date             date      ,
	notes                varchar(500)      ,
	total_before_discount decimal(12,2)  NOT NULL DEFAULT 0   ,
	discount_total       decimal(12,2)  NOT NULL DEFAULT 0   ,
	total_due            decimal(12,2)  NOT NULL DEFAULT 0   ,
	status               enum  NOT NULL DEFAULT 'ISSUED'   ,
	created_at           date   DEFAULT CURRENT_DATE   ,
	updated_at           date      ,
	school_id            int      ,
	student_id           int      ,
	CONSTRAINT unq_invoice_school_id UNIQUE ( school_id ) ,
	CONSTRAINT unq_invoice_student_id UNIQUE ( student_id ) 
 ) engine=InnoDB;

ALTER TABLE pramuksankalp.invoice MODIFY total_before_discount decimal(12,2)  NOT NULL DEFAULT 0  COMMENT 'subtotal';

ALTER TABLE pramuksankalp.invoice MODIFY discount_total decimal(12,2)  NOT NULL DEFAULT 0  COMMENT 'persantage + flat all discount total';

ALTER TABLE pramuksankalp.invoice MODIFY total_due decimal(12,2)  NOT NULL DEFAULT 0  COMMENT 'total_before_discount - discount_total';

ALTER TABLE pramuksankalp.invoice MODIFY status enum  NOT NULL DEFAULT 'ISSUED'  COMMENT '''DRAFT'',\n''ISSUED'',\n''PARTIAL'',\n''PAID'',\n''VOID''';

CREATE TABLE pramuksankalp.ms_relation ( 
	relation_id          int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	relation             varchar(100)  NOT NULL    ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.permission ( 
	permission_id        int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	permkey              smallint  NOT NULL    ,
	create_at            datetime  NOT NULL DEFAULT CURRENT_TIMESTAMP   ,
	update_at            date      ,
	permname             varchar(500)  NOT NULL    
 ) engine=InnoDB;

ALTER TABLE pramuksankalp.permission MODIFY permkey smallint  NOT NULL   COMMENT 'ex\n10001\n10002\n10003';

CREATE TABLE pramuksankalp.sembatches ( 
	sembatches_id        int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	start_date           date      ,
	end_date             date      ,
	capacity             int      ,
	next_sembatch_id     int      ,
	fees                 decimal(10,2)  NOT NULL    ,
	code_prefix          varchar(255)  NOT NULL    ,
	status               tinyint  NOT NULL DEFAULT 0   ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.student ( 
	student_id           int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	studenttype_id       int  NOT NULL    ,
	satsangmandal_id     int      ,
	shikharm_id          int      ,
	kshetra_id           int      ,
	eduboard_id          int  NOT NULL    ,
	std_id               int  NOT NULL    ,
	school_id            int      ,
	takula_id            int  NOT NULL    ,
	fname                varchar(100)  NOT NULL    ,
	mname                varchar(100)  NOT NULL    ,
	lname                varchar(100)  NOT NULL    ,
	gender               varchar(10)      ,
	email                varchar(255)  NOT NULL    ,
	birthdate            date      ,
	address              varchar(500)      ,
	city_village         varchar(50)      ,
	pincode              varchar(10)      ,
	contact_no           varchar(10)      ,
	whatsapp_no          varchar(10)      ,
	educationmedium      enum      ,
	institute            varchar(500)      ,
	achievement_stsang   text      ,
	achievement_academic text      ,
	activities           text      ,
	learnabout           enum      ,
	materiallanguage     enum      ,
	taluka_id_postal     int      ,
	city_postal          int      ,
	address_postal       varchar(500)      ,
	notes                text      ,
	refby                varchar(100)      ,
	createdby            varchar(100)      ,
	lmsuser              varchar(255)      ,
	lmspswd              varchar(255)      ,
	CONSTRAINT unq_student_studenttype_id UNIQUE ( studenttype_id ) ,
	CONSTRAINT unq_student_satsangmandal_id UNIQUE ( satsangmandal_id ) ,
	CONSTRAINT unq_student_shikharm_id UNIQUE ( shikharm_id ) ,
	CONSTRAINT unq_student_kshetra_id UNIQUE ( kshetra_id ) ,
	CONSTRAINT unq_student_eduboard_id UNIQUE ( eduboard_id ) ,
	CONSTRAINT unq_student_std_id UNIQUE ( std_id ) ,
	CONSTRAINT unq_student_school_id UNIQUE ( school_id ) ,
	CONSTRAINT unq_student_takula_id UNIQUE ( takula_id ) 
 ) engine=InnoDB;

ALTER TABLE pramuksankalp.student MODIFY gender varchar(10)     COMMENT 'M - Male\nF - Female\nO - Other';

ALTER TABLE pramuksankalp.student MODIFY educationmedium enum     COMMENT '1 - Gujarati\n2 - Hindi\n3 - English\n4 - Other';

ALTER TABLE pramuksankalp.student MODIFY learnabout enum     COMMENT '1- From BAPS Magazines – સંસ્થાના પ્રકાશનો પરથી\n2-From Social Media – સોશિયલ મીડિયા પરથી\n3- From Satsang Sabha – સત્સંગ સભામાંથી\n4- From an Individual – કોઈ વ્યક્તિ પાસેથી';

ALTER TABLE pramuksankalp.student MODIFY materiallanguage enum     COMMENT '1 - English\n2 - Hindi';

CREATE TABLE pramuksankalp.studenttype ( 
	studenttype_id       int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	studenttypename      varchar(100)  NOT NULL    ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      
 ) engine=InnoDB;

ALTER TABLE pramuksankalp.studenttype MODIFY studenttypename varchar(100)  NOT NULL   COMMENT 'baps\nnonbaps';

CREATE TABLE pramuksankalp.sysuser ( 
	sysuser_id           int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	role_id              int  NOT NULL    ,
	fname                varchar(100)  NOT NULL    ,
	lname                varchar(100)      ,
	mname                varchar(100)      ,
	email                varchar(250)      ,
	phone                varchar(10)      ,
	address1             varchar(250)      ,
	address2             varchar(250)      ,
	city                 varchar(100)      ,
	pincode              varchar(10)      ,
	whatsapp             varchar(10)      ,
	pswd                 varchar(150)  NOT NULL    ,
	pin                  varchar(4)      ,
	inactive             bit  NOT NULL DEFAULT 0   ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      ,
	createdby            varchar(100)      ,
	username             varchar(255)  NOT NULL    ,
	refcode              varchar(100)      ,
	CONSTRAINT unq_systemuser_role_id UNIQUE ( role_id ) 
 ) engine=InnoDB;

ALTER TABLE pramuksankalp.sysuser MODIFY pin varchar(4)     COMMENT 'set 4 digit pin';

CREATE TABLE pramuksankalp.usertype ( 
	usertype_id          int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	usertypename         varchar(200)  NOT NULL    ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      
 ) engine=InnoDB;

ALTER TABLE pramuksankalp.usertype MODIFY usertypename varchar(200)  NOT NULL   COMMENT 'ex.\nstudent\nadmin\ninitiator';

CREATE TABLE pramuksankalp.ms_kshetra ( 
	kshetra_id           int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	kshetraname          varchar(100)  NOT NULL    ,
	santname             varchar(100)      ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.ms_satsangmandal ( 
	satsangmandal_id     int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	mandalname           varchar(100)      ,
	karyakarname         varchar(100)      ,
	karyakarcontact      varchar(20)      ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.ms_shikharmandir ( 
	shikharm_id          int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	mname                varchar(100)  NOT NULL    ,
	maddress             varchar(500)      ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.ms_standard ( 
	std_id               int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	stdname              varchar(100)  NOT NULL    ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.role ( 
	role_id              int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	rolename             varchar(100)  NOT NULL    ,
	ispredefined         bit   DEFAULT 0   ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.school ( 
	school_id            int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	schooltype_id        int  NOT NULL    ,
	eduboard_id          int  NOT NULL    ,
	schoolname           varchar(1000)  NOT NULL    ,
	logourl              varchar(1000)      ,
	address1             varchar(255)      ,
	address2             varchar(255)      ,
	city_village         varchar(100)      ,
	takula_id            int  NOT NULL    ,
	pincode              varchar(10)      ,
	phone                varchar(15)      ,
	mobile               varchar(10)      ,
	email                varchar(150)      ,
	website              varchar(255)      ,
	establishedyear      int      ,
	adminname            varchar(200)  NOT NULL    ,
	admincontact         varchar(10)  NOT NULL    ,
	inactive             bit  NOT NULL DEFAULT 0   ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      ,
	createdby            varchar(100)      ,
	status               smallint      ,
	CONSTRAINT unq_school_schooltype_id UNIQUE ( schooltype_id ) ,
	CONSTRAINT unq_school_takula_id UNIQUE ( takula_id ) ,
	CONSTRAINT unq_school_eduboard_id UNIQUE ( eduboard_id ) 
 ) engine=InnoDB;

ALTER TABLE pramuksankalp.school MODIFY logourl varchar(1000)     COMMENT 'URL or path to the school’s logo (optional).';

ALTER TABLE pramuksankalp.school MODIFY status smallint     COMMENT '0-temp\n1-final';

CREATE TABLE pramuksankalp.schooltype ( 
	schooltype_id        int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	schooltypename       varchar(100)      ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      
 ) engine=InnoDB;

ALTER TABLE pramuksankalp.schooltype MODIFY schooltypename varchar(100)     COMMENT 'e.g., \nPublic, \nPrivate, \nCharter.';

CREATE TABLE pramuksankalp.ms_board ( 
	board_id             int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	boardname            varchar(255)  NOT NULL    ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      
 ) engine=InnoDB;

ALTER TABLE pramuksankalp.ms_board MODIFY boardname varchar(255)  NOT NULL   COMMENT 'e.g., \nCBSE, \nICSE, \nState Board';

CREATE TABLE pramuksankalp.ms_eductionalboard ( 
	eduboard_id          int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	boardname            varchar(100)      ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      
 ) engine=InnoDB;

ALTER TABLE pramuksankalp.ms_eductionalboard MODIFY boardname varchar(100)     COMMENT 'CBSE, \nICSE, \nState Board,\netc';

CREATE TABLE pramuksankalp.ms_taluko ( 
	takula_id            int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	dist_id              int  NOT NULL    ,
	tname                varchar(100)  NOT NULL    ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      ,
	CONSTRAINT unq_ms_taluko_dist_id UNIQUE ( dist_id ) 
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.ms_dist ( 
	dist_id              int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	state_id             int  NOT NULL    ,
	dname                varchar(100)  NOT NULL    ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      ,
	CONSTRAINT unq_ms_dist_state_id UNIQUE ( state_id ) 
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.ms_state ( 
	state_id             int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	country_id           int  NOT NULL    ,
	sname                varchar(100)  NOT NULL    ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      ,
	CONSTRAINT unq_ms_state_country_id UNIQUE ( country_id ) 
 ) engine=InnoDB;

CREATE TABLE pramuksankalp.ms_country ( 
	country_id           int  NOT NULL  AUTO_INCREMENT  PRIMARY KEY,
	cname                varchar(100)  NOT NULL    ,
	create_at            date  NOT NULL DEFAULT CURRENT_DATE   ,
	update_at            date      
 ) engine=InnoDB;

ALTER TABLE pramuksankalp.commissionbatch ADD CONSTRAINT fk_commissionbatch_usercommission FOREIGN KEY ( cbatch_id ) REFERENCES pramuksankalp.usercommission( cbatch_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.commissionbatch ADD CONSTRAINT fk_commissionbatch_lg_commissionbatch FOREIGN KEY ( cbatch_id ) REFERENCES pramuksankalp.lg_commissionbatch( current ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.`fee_plan ` ADD CONSTRAINT `fk_fee_plan _invoice_item ` FOREIGN KEY ( `fee_plan_id ` ) REFERENCES pramuksankalp.`invoice_item `( `fee_plan_id ` ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.guardian ADD CONSTRAINT fk_guardian_guardian_lang FOREIGN KEY ( guardian_id ) REFERENCES pramuksankalp.guardian_lang( guardian_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.interview ADD CONSTRAINT fk_interview_lg_nterview FOREIGN KEY ( interview_id ) REFERENCES pramuksankalp.lg_nterview( interview_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.invoice ADD CONSTRAINT `fk_invoice_invoice_item ` FOREIGN KEY ( invoice_id ) REFERENCES pramuksankalp.`invoice_item `( invoice_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.invoice ADD CONSTRAINT fk_invoice_paymentlog FOREIGN KEY ( invoice_id ) REFERENCES pramuksankalp.paymentlog( invoice_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.language ADD CONSTRAINT fk_language_guardian_lang FOREIGN KEY ( lg_id ) REFERENCES pramuksankalp.guardian_lang( lg_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.ms_board ADD CONSTRAINT fk_ms_board_school FOREIGN KEY (  ) REFERENCES pramuksankalp.school(  ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.ms_country ADD CONSTRAINT fk_ms_country_ms_state FOREIGN KEY ( country_id ) REFERENCES pramuksankalp.ms_state( country_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.ms_dist ADD CONSTRAINT fk_ms_dist_ms_taluko FOREIGN KEY ( dist_id ) REFERENCES pramuksankalp.ms_taluko( dist_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.ms_eductionalboard ADD CONSTRAINT fk_ms_eductionalboard_student FOREIGN KEY ( eduboard_id ) REFERENCES pramuksankalp.student( eduboard_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.ms_eductionalboard ADD CONSTRAINT fk_ms_eductionalboard_school FOREIGN KEY ( eduboard_id ) REFERENCES pramuksankalp.school( eduboard_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.ms_kshetra ADD CONSTRAINT fk_ms_kshetra_student FOREIGN KEY ( kshetra_id ) REFERENCES pramuksankalp.student( kshetra_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.ms_relation ADD CONSTRAINT fk_ms_relation_guardian FOREIGN KEY ( relation_id ) REFERENCES pramuksankalp.guardian( relation_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.ms_satsangmandal ADD CONSTRAINT fk_ms_satsangmandal_student FOREIGN KEY ( satsangmandal_id ) REFERENCES pramuksankalp.student( satsangmandal_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.ms_shikharmandir ADD CONSTRAINT fk_ms_shikharmandir_student FOREIGN KEY ( shikharm_id ) REFERENCES pramuksankalp.student( shikharm_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.ms_standard ADD CONSTRAINT fk_ms_standard_student FOREIGN KEY ( std_id ) REFERENCES pramuksankalp.student( std_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.ms_standard ADD CONSTRAINT `fk_ms_standard_fee_plan ` FOREIGN KEY ( std_id ) REFERENCES pramuksankalp.`fee_plan `( std_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.ms_state ADD CONSTRAINT fk_ms_state_ms_dist FOREIGN KEY ( state_id ) REFERENCES pramuksankalp.ms_dist( state_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.ms_taluko ADD CONSTRAINT fk_ms_taluko_student FOREIGN KEY ( takula_id ) REFERENCES pramuksankalp.student( takula_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.ms_taluko ADD CONSTRAINT fk_ms_taluko_school FOREIGN KEY ( takula_id ) REFERENCES pramuksankalp.school( takula_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.parcelcompany ADD CONSTRAINT fk_parcelcompany_parcel FOREIGN KEY ( parcelcom_id ) REFERENCES pramuksankalp.parcel( parcelcom_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.paymentmode ADD CONSTRAINT fk_paymentmode_paymentlog FOREIGN KEY ( paymode_id ) REFERENCES pramuksankalp.paymentlog( paymode_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.permission ADD CONSTRAINT fk_permission_rolepermission FOREIGN KEY ( permission_id ) REFERENCES pramuksankalp.rolepermission( permission_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.role ADD CONSTRAINT fk_role_rolepermission FOREIGN KEY ( role_id ) REFERENCES pramuksankalp.rolepermission( role_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.role ADD CONSTRAINT fk_role_systemuser FOREIGN KEY ( role_id ) REFERENCES pramuksankalp.sysuser( role_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.school ADD CONSTRAINT fk_school_userschool FOREIGN KEY ( school_id ) REFERENCES pramuksankalp.userschool( school_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.school ADD CONSTRAINT fk_school_usercommission FOREIGN KEY ( school_id ) REFERENCES pramuksankalp.usercommission( school_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.school ADD CONSTRAINT fk_school_paymentlog FOREIGN KEY (  ) REFERENCES pramuksankalp.paymentlog(  ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.school ADD CONSTRAINT fk_school_student FOREIGN KEY ( school_id ) REFERENCES pramuksankalp.student( school_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.school ADD CONSTRAINT `fk_school_fee_plan ` FOREIGN KEY ( school_id ) REFERENCES pramuksankalp.`fee_plan `( valid_from ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.school ADD CONSTRAINT `fk_school_fee_plan _0` FOREIGN KEY ( school_id ) REFERENCES pramuksankalp.`fee_plan `( school_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.school ADD CONSTRAINT `fk_school_fee_plan _1` FOREIGN KEY ( school_id ) REFERENCES pramuksankalp.`fee_plan `( school_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.school ADD CONSTRAINT fk_school_discount_rule FOREIGN KEY ( school_id ) REFERENCES pramuksankalp.discount_rule( school_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.school ADD CONSTRAINT fk_school_invoice FOREIGN KEY ( school_id ) REFERENCES pramuksankalp.invoice( school_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.schooltype ADD CONSTRAINT fk_schooltype_school FOREIGN KEY ( schooltype_id ) REFERENCES pramuksankalp.school( schooltype_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.sembatches ADD CONSTRAINT fk_sembatches_student_sembatches FOREIGN KEY ( sembatches_id ) REFERENCES pramuksankalp.student_sembatches( sembatches_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.sembatches ADD CONSTRAINT `fk_sembatches_fee_plan ` FOREIGN KEY ( sembatches_id ) REFERENCES pramuksankalp.`fee_plan `( created_at ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.student ADD CONSTRAINT fk_student_paymentlog FOREIGN KEY (  ) REFERENCES pramuksankalp.paymentlog(  ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.student ADD CONSTRAINT fk_student_parcel FOREIGN KEY ( student_id ) REFERENCES pramuksankalp.parcel( student_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.student ADD CONSTRAINT fk_student_guardian FOREIGN KEY ( student_id ) REFERENCES pramuksankalp.guardian( student_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.student ADD CONSTRAINT fk_student_document FOREIGN KEY ( student_id ) REFERENCES pramuksankalp.document( student_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.student ADD CONSTRAINT fk_student_student_sembatches FOREIGN KEY ( student_id ) REFERENCES pramuksankalp.student_sembatches( student_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.student ADD CONSTRAINT fk_student_student_lang FOREIGN KEY ( student_id ) REFERENCES pramuksankalp.student_lang( student_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.student ADD CONSTRAINT fk_student_lg_intervie FOREIGN KEY ( student_id ) REFERENCES pramuksankalp.interview( student_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.student ADD CONSTRAINT fk_student_userstudent FOREIGN KEY ( student_id ) REFERENCES pramuksankalp.userstudent( sysuser_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.student ADD CONSTRAINT fk_student_userstudent_0 FOREIGN KEY ( student_id ) REFERENCES pramuksankalp.userstudent( student_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.student ADD CONSTRAINT fk_student_discount_rule FOREIGN KEY ( student_id ) REFERENCES pramuksankalp.discount_rule( student_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.student ADD CONSTRAINT fk_student_invoice FOREIGN KEY ( student_id ) REFERENCES pramuksankalp.invoice( student_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.studenttype ADD CONSTRAINT fk_studenttype_student FOREIGN KEY ( studenttype_id ) REFERENCES pramuksankalp.student( studenttype_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.sysuser ADD CONSTRAINT fk_systemuser_userschool FOREIGN KEY ( sysuser_id ) REFERENCES pramuksankalp.userschool( sysuser_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.sysuser ADD CONSTRAINT fk_systemuser_usercommission FOREIGN KEY ( sysuser_id ) REFERENCES pramuksankalp.usercommission( sysuser_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.sysuser ADD CONSTRAINT fk_sysuser_lg_sysuser FOREIGN KEY ( sysuser_id ) REFERENCES pramuksankalp.lg_sysuser( sysuser_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.sysuser ADD CONSTRAINT fk_sysuser_lg_intervie FOREIGN KEY ( sysuser_id ) REFERENCES pramuksankalp.interview( sysuser_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.sysuser ADD CONSTRAINT fk_sysuser_userstudent FOREIGN KEY ( sysuser_id ) REFERENCES pramuksankalp.userstudent( sysuser_id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE pramuksankalp.usertype ADD CONSTRAINT fk_usertype_systemuser FOREIGN KEY (  ) REFERENCES pramuksankalp.sysuser(  ) ON DELETE NO ACTION ON UPDATE NO ACTION;
