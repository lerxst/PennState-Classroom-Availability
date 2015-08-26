CREATE TABLE IF NOT EXISTS classroom_associations (
	psu_room_id INT NOT NULL PRIMARY KEY,
	classroom_name VARCHAR(256) NOT NULL
) ENGINE=InnoDB;

INSERT INTO classroom_associations(psu_room_id,classroom_name) VALUES(260,'113 IST - Cybertorium');
INSERT INTO classroom_associations(psu_room_id,classroom_name) VALUES(261,'203 IST');
INSERT INTO classroom_associations(psu_room_id,classroom_name) VALUES(262,'210 IST');
INSERT INTO classroom_associations(psu_room_id,classroom_name) VALUES(471,'110 IST');
INSERT INTO classroom_associations(psu_room_id,classroom_name) VALUES(472,'202 IST');
INSERT INTO classroom_associations(psu_room_id,classroom_name) VALUES(473,'205 IST');
INSERT INTO classroom_associations(psu_room_id,classroom_name) VALUES(474,'206 IST');
INSERT INTO classroom_associations(psu_room_id,classroom_name) VALUES(475,'208 IST');

CREATE TABLE IF NOT EXISTS classroom_schedules (
	psu_room_id INT NOT NULL,
	start_time DATETIME NOT NULL,
	end_time DATETIME NOT NULL,
	class_name VARCHAR(512) NOT NULL,
	PRIMARY KEY (psu_room_id, start_time, end_time),
	CONSTRAINT classroom_schedules_fk_rooms FOREIGN KEY (psu_room_id) REFERENCES classroom_associations(psu_room_id)
) ENGINE=InnoDB;
