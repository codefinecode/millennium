create table user
(
    id          int auto_increment
        primary key,
    first_name  varchar(255) charset utf8mb4 null,
    second_name varchar(255) charset utf8mb4 null,
    birthday    date                         null,
    created_at  datetime                     null
);

INSERT INTO user (id, first_name, second_name, birthday, created_at) VALUES (1, 'Петр', 'Петров', '2000-04-14', '2024-02-01 18:17:16');
INSERT INTO user (id, first_name, second_name, birthday, created_at) VALUES (2, 'Иван', 'Иванов', '1997-06-17', '2024-02-02 18:17:43');
