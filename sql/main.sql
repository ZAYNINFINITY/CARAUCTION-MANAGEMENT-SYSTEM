create database car_auction;

use car_auction;

create table user_country (
    country_id int auto_increment primary key,
    country_name varchar(100) not null
) engine=innodb;

create table color (
    color_id int auto_increment primary key,
    color_name varchar(100) not null
) engine=innodb;


create table users (
    user_id int auto_increment primary key,
    user_name varchar(100) not null,
    email varchar(100) unique not null,
    role enum('seller','buyer') not null,
    country_id int,
    foreign key (country_id) references user_country(country_id) on delete set null on update cascade
) engine=innodb;

create table car (
    car_id int auto_increment primary key,
    user_id int not null,
    color_id int,
    make varchar(100) not null,
    model varchar(100) not null,
    year int not null,
    mileage int not null,
    base_price decimal(10,2) not null,
    predicted_price decimal(10,2),
    foreign key (user_id) references users(user_id) on delete cascade on update cascade,
    foreign key (color_id) references color(color_id) on delete set null on update cascade
) engine=innodb;

create table auction (
    auction_id int auto_increment primary key,
    car_id int not null,
    start_date datetime default current_timestamp,
    end_date datetime,
    status enum('active','closed') default 'active',
    foreign key (car_id) references car(car_id) on delete cascade on update cascade
) engine=innodb;


create table bid (
    bid_id int auto_increment primary key,
    auction_id int not null,
    buyer_id int not null,
    amount decimal(10,2) not null,
    created_at timestamp default current_timestamp,
    foreign key (auction_id) references auction(auction_id) on delete cascade on update cascade,
    foreign key (buyer_id) references users(user_id) on delete cascade on update cascade
) engine=innodb;

create table transactions (
    transaction_id int auto_increment primary key,
    auction_id int not null unique,
    buyer_id int not null,
    final_price decimal(10,2) not null,
    transaction_date datetime not null default current_timestamp,
    payment_method varchar(50) default 'unknown',
    foreign key (auction_id) references auction(auction_id) on delete cascade on update cascade,
    foreign key (buyer_id) references users(user_id) on delete cascade on update cascade
) engine=innodb;


rename table  transaction to  transactions;


alter table  transactions modify  transaction_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  MODIFY payment_method VARCHAR(50) DEFAULT 'unknown';

insert into user_country (country_name) values
('uae'), ('china'), ('india'), ('jordan'), ('germany'),
('philippines'), ('saudi arabia'), ('south korea'), ('russia'),
('turkey'), ('usa'), ('canada'), ('brazil');

insert into color (color_name) values
('black'), ('white'), ('silver'), ('red'), ('blue'), ('gray'),
('green'), ('yellow');

--seller 
insert into users (user_name, email, role, country_id) values
('ahmed al-farsi', 'ahmed@uae.com', 'seller', 1),
('li wei', 'liwei@china.com', 'seller', 2),
('raj patel', 'raj@india.com', 'seller', 3),
('fatima hassan', 'fatima@jordan.com', 'seller', 4),
('hans mueller', 'hans@germany.com', 'seller', 5),
('maria santos', 'maria@philippines.com', 'seller', 6),
('omar khalid', 'omar@saudi.com', 'seller', 7),
('kim ji-hoon', 'kim@southkorea.com', 'seller', 8),
('elena petrova', 'elena@russia.com', 'seller', 9),
('yusuf demir', 'yusuf@turkey.com', 'seller', 10);

--buyer
insert into users (user_name, email, role, country_id) values
('alice johnson', 'alice@example.com', 'buyer', 11),
('bob smith', 'bob@example.com', 'buyer', 11),
('charlie brown', 'charlie@example.com', 'buyer', 12),
('diana prince', 'diana@example.com', 'buyer', 13),
('eve wilson', 'eve@example.com', 'buyer', 11);

insert into users (user_name, email, role, country_id) values


insert into car (user_id, color_id, make, model, year, mileage, base_price, predicted_price) values
(1, 1, 'toyota', 'camry', 2019, 45000, 18000.00, 18500.00),
(2, 2, 'honda', 'accord', 2020, 35000, 20000.00, 20500.00),
(3, 3, 'bmw', '3 series', 2018, 55000, 25000.00, 24800.00),
(4, 4, 'mercedes-benz', 'c-class', 2017, 60000, 22000.00, 21500.00),
(5, 5, 'audi', 'a4', 2019, 40000, 24000.00, 24100.00),
(6, 6, 'nissan', 'altima', 2021, 25000, 19000.00, 19300.00),
(7, 1, 'hyundai', 'sonata', 2020, 30000, 17000.00, 17200.00),
(8, 2, 'kia', 'optima', 2019, 38000, 16000.00, 16100.00),
(9, 3, 'volkswagen', 'passat', 2018, 50000, 15000.00, 15200.00),
(10, 4, 'mazda', 'mazda6', 2020, 32000, 21000.00, 21400.00);
--
insert into car (user_id, color_id, make, model, year, mileage, base_price, predicted_price) values
(16, 1, 'ford', 'fusion', 2019, 42000, 17000.00, 17300.00),
(17, 2, 'chevrolet', 'malibu', 2018, 47000, 16000.00, 16250.00),
(1, 3, 'subaru', 'legacy', 2020, 30000, 19000.00, 19250.00),
(2, 4, 'volvo', 's60', 2019, 35000, 22000.00, 22300.00),
(3, 5, 'jaguar', 'xe', 2018, 40000, 25000.00, 24850.00),
(4, 6, 'lexus', 'es', 2021, 20000, 35000.00, 35200.00),
(5, 1, 'mitsubishi', 'galant', 2017, 55000, 14000.00, 14500.00),
(6, 2, 'infiniti', 'q50', 2020, 28000, 27000.00, 27250.00),
(7, 3, 'renault', 'talisman', 2019, 33000, 18000.00, 18250.00),
(8, 4, 'peugeot', '508', 2018, 45000, 16000.00, 16200.00);


select user_id from users;

insert into auction (car_id, start_date, end_date, status) values
(1, now(), date_add(now(), interval 7 day), 'active'),
(2, now(), date_add(now(), interval 7 day), 'active'),
(3, now(), date_add(now(), interval 7 day), 'active'),
(4, now(), date_add(now(), interval 7 day), 'active'),
(5, now(), date_add(now(), interval 7 day), 'active'),
(6, now(), date_add(now(), interval 7 day), 'active'),
(7, now(), date_add(now(), interval 7 day), 'active'),
(8, now(), date_add(now(), interval 7 day), 'active'),
(9, now(), date_add(now(), interval 7 day), 'active'),
(10, now(), date_add(now(), interval 7 day), 'active');

insert into bid (auction_id, buyer_id, amount, created_at) values
(1, 11, 19000.00, now() - interval 2 day),
(1, 12, 19500.00, now() - interval 1 day),
(2, 13, 21000.00, now() - interval 3 day),
(3, 14, 26000.00, now() - interval 1 day),
(4, 15, 23000.00, now() - interval 2 day);


insert into transaction (auction_id, buyer_id, final_price, transaction_date) values
(1, 12, 19500.00, now() - interval 1 hour),
(2, 13, 21000.00, now() - interval 2 hour);

select * from car;
insert into car (user_id, color_id, make, model, year, mileage, base_price)
values (10, 7, 'toyota ', 'mark x', 2019, 40000, 25000);
update auction set status = 'closed' where auction_id = 4;
select * from auction;



/*
-- lab task
use car_auction;

select count(*) from car;

select count(*) as total_cars from car;

select sum(base_price) from car;

select max(base_price) from car;

select min(base_price) from car;

select min(base_price) as lowest_price from car;

select avg(base_price) from car;

select make, group_concat(model) from car group by make order by make;

select make, avg(base_price) from car group by make having avg(base_price) > 20000 order by avg(base_price) desc;

select year, count(car_id) from car group by year having count(car_id) > 1 order by year desc;



update user_country set country_name = 'algeria' where country_id = 5;

select * from car where make like  '%a';

select * from car where make like  'a%';

select * from car where model like  '_______%';

select * from car where model like  '_______';

select * from car as c inner join auction  as a on a.car_id = c.car_id;

select c.make, c.year, a.* from car as c inner join auction as a on c.car_id = a.car_id;

select c.make, c.model, c.year, a.*from car as c inner join auction as a on c.car_id = a.car_id;

select c.mileage, c.make, a.* from car as c right join auction as a on c.car_id = a.car_id;

select a.auction_id, a.car_id  from car as c right join auction as a on a.car_id = c.car_id where c.car_id is null;

select car_id from car union select car_id from auction;

select c.car_id, c.make, c.model from car as c left join auction as a on c.car_id = a.car_id where a.car_id is null;


select c.car_id, c.make, c.model, a.auction_id
from car c
left join auction a on c.car_id = a.car_id
union
select c.car_id, c.make, c.model, null as auction_id
from car c
right join auction a on c.car_id = a.car_id
where a.car_id is not null
order by car_id, make; 
-- commented out to avoid creating table
-- create table dummy (
--     id int primary key,
--     name varchar(50)
-- );
*/

