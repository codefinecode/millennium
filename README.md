# Запуск проекта

```
git clone https://github.com/codefinecode/millenium.git  
cd millenium
composer install
```
скопировать/переименовать `.env.example` в `.env`  
и в нем настроить подключение к БД  

в разработке использован локальный домен `millenium-tz-240621.local` - можно заменить на любой удобный.  
настроить сервер на папку `public` проекта.  
Запустить в браузере `http://millenium-tz-240621.local` (либо свой настроенный домен)  
Изначально будет алерт с ошибкой (поскольку база пуста). Необходимо нажать кнопку `Выполнить первичные миграции` 

## Пояснения
### По UI/UX фронта
инпут `Ввод Client ID:` для id клиента (их всего два по дефолту, автоматом выбирается второй).  
в таблице список заказов клиента.  
Текстовое поле `Добавить продукты` принимает json с массивом продуктов (пример в дефолтном значении)
Кнопка `Выполнить первичные миграции` описана выше.  
Кнопка `Очистить базу` дропает все таблицы в базе.  

### разархивации/миграции/оптимизации
(реализован паттерн стратегия для работы с архиваторами)     
В папку `/data` разместить архив `products.zip` с дампами sql, они (только файлы с расширениями ".sql") распакуются в `/storage`

### Для тестирования запросов:
корневой маршрут отдает html с фронтом
`curl -X GET http://millenium-tz-240621.local/index.php`  

получить заказы клиента с id=2 в json   
`curl -X GET http://millenium-tz-240621.local/index.php?client_id=2`

Добавить продукт/подукты - методом POST отправить JSON массив (в данном примере один продукт в массиве)  
`curl -X POST -d '[{"title": "Product Title", "price": 99.99}]' http://millenium-tz-240621.local/index.php`  

Дропнуть таблицы в базе  
`curl -X GET http://millenium-tz-240621.local/index.php?flush_database=password`  