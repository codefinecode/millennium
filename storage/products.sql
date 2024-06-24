create table products
(
    id    int auto_increment
        primary key,
    title varchar(255) charset utf8mb4 null,
    price decimal(10, 2)               null
);

INSERT INTO products (id, title, price) VALUES (1, 'Три товарища', 49.80);
INSERT INTO products (id, title, price) VALUES (2, 'Триумфальная арка', 349.00);
INSERT INTO products (id, title, price) VALUES (3, 'Один год жизни', 149.00);
INSERT INTO products (id, title, price) VALUES (4, 'Северный дракон', 249.00);
