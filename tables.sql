Create or Replace Function repairItemCount
Return Integer is
l_cnt Integer;
BEGIN
	SELECT count(*) INTO l_cnt
	FROM repairitems;
	return l_cnt;
	commit;

END;
/

drop table repairlog;
drop table done_repairjob;
drop table ready_repairjob;
drop table customerbill;
drop table repairperson;
drop table listproblems;
drop table problemreport;
drop table repairjob;
drop table groupcontract;
drop table singlecontract;
drop table servicecontract;
drop table printers;
drop table computers;
drop table repairitems;
drop table customer;

create table customer(
	name varchar(30),
	phone varchar(15) primary key
);
create table repairitems(
	itemid varchar(10) primary key,
	model varchar(20),
	price integer,
	year integer,
	contracttype varchar(6),
	CHECK(contracttype in ('NONE', 'SINGLE', 'GROUP')),
	custname varchar(30),
	custphone varchar(15)
);
create table computers(
	itemid varchar(10),
	model varchar(20),
	foreign key (itemid) references repairitems (itemid),
	primary key (itemid)
);
create table printers(
	itemid varchar(10),
	model varchar(20),
	foreign key (itemid) references repairitems (itemid),
	primary key (itemid)
);
create table servicecontract(
	contractid varchar(10) primary key,
	machineid1 varchar(10),
	machineid2 varchar(10),
	startdate varchar(50),
	enddate varchar(50),
	custname varchar(30),
	custphone varchar(15)
	-- foreign key (custname, custphone) references customer (name, phone)
	--foreign key (machineid1) references repairitems (itemid),
	--foreign key (machineid2) references repairitems (itemid)
);
create table singlecontract(
	contractid varchar(10) primary key,
	machineid varchar(10),
	foreign key (contractid) references servicecontract(contractid)
);
create table groupcontract(
	contractid varchar(10) primary key,
	machineid1 varchar(10),
	machineid2 varchar(10),
	foreign key (contractid) references servicecontract(contractid)
);
create table repairjob(
	machineid varchar(10) primary key,
	contractid varchar(10),
	arrivaltime varchar(50),
	departtime varchar(50),
	hours integer,
	costofparts integer,
	status varchar(15),
	check(status in ('UNDER_REPAIR', 'READY', 'DONE')),
	ownername varchar(30),
	ownerphone varchar(15),
	empno varchar(10),
	-- foreign key (ownername, ownerphone) references customer (name, phone),
	foreign key (machineid) references repairitems (itemid),
 	foreign key (contractid) references servicecontract (contractid)
);
create table problemreport(
	itemid varchar(10),
	probid varchar(10),
	problem varchar(30),
	primary key (itemid, probid),
	foreign key (itemid) references repairitems (itemid)
);
create table listproblems(
	probid varchar(10) primary key,
	problem varchar(30)
);
create table repairperson(
	empno varchar(10) primary key,
	name varchar(30),
	phone varchar(15)
);
create table customerbill(
	machineid varchar(10) primary key,
	model varchar(20),
	custname varchar(30),
	custphone varchar(15),
	timein varchar(50),
	timeout varchar(50),
	probid varchar(10),
	problem varchar(30),
	empno varchar(10),
	hours integer,
	costofparts integer,
	totalcharge integer,
	foreign key (machineid) references repairitems (itemid),	
	foreign key (empno) references repairperson (empno)
);
create table ready_repairjob(
	machineid varchar(10) primary key
);
create table done_repairjob(
	machineid varchar(10) primary key
);
create table repairlog(
	machineid varchar(10) primary key,
	contractid varchar(10),
	arrivaltime varchar(50),
	departtime varchar(50),
	hours integer,
	costofparts integer,
	status varchar(15),
	check(status in ('DONE')),
	ownername varchar(30),
	ownerphone varchar(15),
	empno varchar(10),
	-- foreign key (ownername, ownerphone) references customer (name, phone),
	foreign key (machineid) references repairitems (itemid),
 	foreign key (contractid) references servicecontract (contractid)
);

set serveroutput on;

CREATE or Replace TRIGGER createRepairJob_trig
	AFTER insert on repairitems

	DECLARE
		CURSOR cur_sc is select * from servicecontract;
		v_sc servicecontract%rowtype;

		CURSOR cur_ri is select * from repairitems;
		v_ri repairitems%rowtype;

		r_itemid repairitems.itemid%type;
		r_name repairitems.custname%type;
		r_phone repairitems.custphone%type;
		r_contractid servicecontract.contractid%type;
		r_empno repairperson.empno%type;
		
		arrival_time varchar(50);
		device varchar(5);

	BEGIN
		r_contractid := NULL;
		arrival_time := to_char(SYSDATE, 'YYYY-MM-DD HH24:MI');

		-- get some attributes of just created repairitem
		OPEN cur_ri;
		LOOP 
			fetch cur_ri into v_ri;
			exit when cur_ri%notfound;
			
			r_itemid := v_ri.itemid;
			r_name := v_ri.custname;
			r_phone := v_ri.custphone;
		END LOOP;
		CLOSE cur_ri;

		-- determine if the repairitem is under a valid contract
		OPEN cur_sc;
		LOOP 
			fetch cur_sc into v_sc;
			exit when cur_sc%notfound;
			
			IF (r_itemid = v_sc.machineid1 OR r_itemid = v_sc.machineid2) AND to_char(SYSDATE, 'YYYY-MM-DD') <= v_sc.enddate THEN
				r_contractid := v_sc.contractid;
			END IF;
		END LOOP;
		CLOSE cur_sc;
		
		SELECT empno into r_empno from( SELECT empno FROM repairperson ORDER BY dbms_random.value ) WHERE rownum = 1;

		-- create the repair job
		Insert into repairjob values (r_itemid, r_contractid, arrival_time, NULL, NULL, NULL, 'UNDER_REPAIR', r_name, r_phone, r_empno);	

		-- enter the repairitem into computer or printer table
		device := substr(r_itemid,1,1);
		OPEN cur_ri;
		LOOP
			FETCH cur_ri INTO v_ri;
			EXIT WHEN cur_ri%NOTFOUND;

			IF r_itemid = v_ri.itemid THEN
				IF device = 'c' THEN
					insert into computers values(v_ri.itemid,v_ri.model);
				ELSE
					insert into printers values(v_ri.itemid,v_ri.model);
				END IF;
			END IF;
		END LOOP;
		CLOSE cur_ri;
		
END createRepairJob_trig;
/
Show errors;

CREATE or Replace TRIGGER fillCustomersTable
	AFTER insert on repairitems

	DECLARE
		CURSOR cur_ri is select * from repairitems;
		v_ri repairitems%rowtype;

	BEGIN
		-- every customer gets add to customer table
		OPEN cur_ri;
		LOOP 
			fetch cur_ri into v_ri;
			exit when cur_ri%notfound;
			
			INSERT INTO customer(name,phone)
			SELECT v_ri.custname, v_ri.custphone FROM dual
		 	WHERE NOT EXISTS (SELECT * FROM customer WHERE name = v_ri.custname and phone = v_ri.custphone);
		END LOOP;
		CLOSE cur_ri;
		
END fillCustomersTable;
/

Show errors;

CREATE OR REPLACE Procedure fillContractsTables AS
	CURSOR cur_sc IS SELECT * FROM servicecontract;
	v_sc servicecontract%ROWTYPE;

	BEGIN
		-- fill single and group contract tables
		OPEN cur_sc;
		LOOP
			FETCH cur_sc INTO v_sc;
			EXIT WHEN cur_sc%NOTFOUND;
			
			IF v_sc.machineid2 IS NULL THEN
				insert into singlecontract values(v_sc.contractid,v_sc.machineid1);
			ELSE
				insert into groupcontract values(v_sc.contractid,v_sc.machineid1,v_sc.machineid2);
			END IF;

		END LOOP;
		CLOSE cur_sc;

END;
/
show errors;

CREATE or Replace TRIGGER statusRepair
	AFTER insert on ready_repairjob

	DECLARE
		CURSOR cur_rpu is select * from ready_repairjob;
		v_rpu ready_repairjob%rowtype;

		CURSOR cur_rp is select * from repairjob;
		v_rp repairjob%rowtype;

		CURSOR cur_lp is select * from listproblems;
		v_lp listproblems%rowtype;

		r_machineid ready_repairjob.machineid%type;
		r_probid problemreport.probid%type;
		r_model repairitems.model%type;

		depart_time varchar(50);
		arrival_time varchar(50);
		r_totalcharge integer := 0;
		hours number;
		r_problem varchar(30);

	BEGIN
		-- get machineid of the row's status that just got changed to REPAIR
		OPEN cur_rpu;
		LOOP 
			fetch cur_rpu into v_rpu;
			exit when cur_rpu%notfound;

			r_machineid := v_rpu.machineid;
		END LOOP;
		CLOSE cur_rpu;

		-- create endtime of repair
		select arrivaltime into arrival_time from repairjob where machineid = r_machineid;
		select hours into hours from repairjob where machineid = r_machineid;
		select TO_CHAR((TO_DATE(arrival_time, 'YYYY-MM-DD HH24:MI' ) + INTERVAL '1' HOUR * hours ), 'YYYY-MM-DD HH24:MI') INTO depart_time FROM DUAL;
		update repairjob set departtime = depart_time where machineid = r_machineid;

		-- get problem code of the machine id
		SELECT probid into r_probid from problemreport where itemid = r_machineid;
		
		-- determine description of problem code for the machineid
		OPEN cur_lp;
		LOOP 
			fetch cur_lp into v_lp;
			exit when cur_lp%notfound;
			
			IF r_probid = v_lp.probid THEN
				update problemreport set problem = v_lp.problem where itemid = r_machineid;			
			END IF;
		END LOOP;
		CLOSE cur_lp;

		-- create customer bill entry
		OPEN cur_rp;
		LOOP 
			fetch cur_rp into v_rp;
			exit when cur_rp%notfound;
			
			IF r_machineid = v_rp.machineid THEN
				SELECT model into r_model from repairitems where itemid = r_machineid;
				select problem into r_problem from problemreport where itemid = r_machineid;
				IF v_rp.contractid IS NULL THEN
					r_totalcharge := v_rp.hours * 20 + 50 + v_rp.costofparts;	
				END IF;
				insert into customerbill values(r_machineid, r_model, v_rp.ownername, v_rp.ownerphone, v_rp.arrivaltime, v_rp.departtime, r_probid, r_problem, v_rp.empno, v_rp.hours, v_rp.costofparts, r_totalcharge);
			END IF;
		END LOOP;
		CLOSE cur_rp;

		delete from ready_repairjob;

END statusRepair;
/
Show errors;

CREATE or Replace TRIGGER statusDone
	AFTER insert on done_repairjob

	DECLARE
		CURSOR cur_dr is select * from done_repairjob;
		v_dr done_repairjob%rowtype;

		r_machineid done_repairjob.machineid%type;	

	BEGIN
		-- get machineid of the row's status that just got changed to DONE
		OPEN cur_dr;
		LOOP 
			fetch cur_dr into v_dr;
			exit when cur_dr%notfound;

			r_machineid := v_dr.machineid;
		END LOOP;
		CLOSE cur_dr;

		INSERT INTO repairlog SELECT * FROM repairjob where machineid = r_machineid;
		DELETE FROM repairjob where machineid = r_machineid;

		delete from done_repairjob;

END statusDone;
/
Show errors;

CREATE OR REPLACE Function calculateRevenue(begindate in varchar, enddate in varchar) RETURN number is total_revenue number;
	
	total_nonwarranty number:=0;
	CURSOR cur_rl IS SELECT * FROM repairlog;
	v_rl repairlog%ROWTYPE;

	BEGIN
		OPEN cur_rl;
		LOOP
			FETCH cur_rl INTO v_rl;
			EXIT WHEN cur_rl%NOTFOUND;
			
			IF v_rl.arrivaltime >= begindate and v_rl.departtime <= enddate THEN
				IF v_rl.contractid IS NULL THEN
					total_nonwarranty := total_nonwarranty + (50 + 20 * v_rl.hours + v_rl.costofparts);
				END IF;
			END IF;

		END LOOP;
		CLOSE cur_rl;
		total_revenue := total_nonwarranty;
		return total_revenue;

END;
/
show errors;

CREATE OR REPLACE Function totalWarrantyCosts(begindate in varchar, enddate in varchar) RETURN number is total_warranty number;

	CURSOR cur_rl IS SELECT * FROM repairlog;
	v_rl repairlog%ROWTYPE;

	warranty number := 0;

	BEGIN
		-- determine costs incurred through warranty
		OPEN cur_rl;
		LOOP
			FETCH cur_rl INTO v_rl;
			EXIT WHEN cur_rl%NOTFOUND;
			
			IF v_rl.departtime >= begindate and v_rl.departtime <= enddate THEN
				IF v_rl.contractid IS NOT NULL THEN
					warranty := warranty + (50 + 20 * v_rl.hours + v_rl.costofparts);
				END IF;
			END IF;

		END LOOP;
		CLOSE cur_rl;
		
		total_warranty := warranty;
		return total_warranty;

END;
/
show errors;

-- database essentials

insert into servicecontract values ('s001', 'c1', NULL, '2016-02-14', '2019-02-14', 'Ilyas Reyhanoglu', '1234567890');
insert into servicecontract values ('s002', 'c3', 'p4', '2017-02-14', '2019-03-14', 'Safwan Alazzway', '4395048672');
insert into servicecontract values ('s003', 'p6', NULL, '2017-02-14', '2019-03-14', 'Stefan Zier', '6504155050');
exec fillContractsTables;

insert into repairperson values ('e001', 'Draymond Curry', '4083333333');
insert into repairperson values ('e002', 'Kevin Thompson', '4084444444');
insert into repairperson values ('e003', 'Paul Harrison', '4088888888');

insert into listproblems values ('r001', 'faulty disc drive');
insert into listproblems values ('r002', 'ink tray exploded');
insert into listproblems values ('r003', 'cpu died');
insert into listproblems values ('r004', 'printer ate my homework');

-- added stuff

commit;














