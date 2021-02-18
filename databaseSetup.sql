create table users(
	user_id int not null primary key auto_increment,
	phone_number varchar(15) not null
);

create table reservations(
	reservation_id int not null primary key auto_increment,
	reservation_token varchar(255) not null,
	user_id int not null,
	reservation_date datetime not null,
	status int,
	adult_count int not null,
	kid_count int,
	foreign key (user_id) references users(user_id)
);
