<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Заказы клиента</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1>Заказы клиента</h1>
            <label for="client_id" class="form-label mt-3">Ввод Client ID:</label>

            <div class="input-group mb-3">
                <input type="number" class="form-control" id="client_id" placeholder="id клиента"
                       style="max-width: 100px">
                <button class="btn btn-primary" onclick="fetchClientData()">Получить заказы</button>
            </div>

            <h3 class="my-3 text-secondary">Клиент: <span id="client_name" class="text-primary"></span></h3>
            <table class="table table-success table-striped table-hover">
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Price</th>
                </tr>
                </thead>
                <tbody id="orders_table"></tbody>
            </table>
            <div>
                <label class="form-label" for="products_json">Добавить продукты</label>
                <textarea class="form-control" rows="7" id="products_json" placeholder="Введите JSON с продуктами">[
    {"title": "Новая книга", "price": 29.99},
    {"title": "Новый смартфон", "price": 699.99},
    {"title": "Новая игра", "price": 49.99}
]
                </textarea>
                <button class="btn btn-primary" onclick="addProducts()">Добавить продукты</button>
            </div>
            <hr>
            <button class="btn btn-primary" onclick="migrate()">Выполнить первичные миграции</button>
            <button class="btn btn-danger" onclick="flushDatabase()">Очистить базу</button>
        </div>
    </div>
</div>


<script>
    async function fetchClientData() {
        const clientId = parseInt(document.getElementById('client_id').value) || 2;

        if (isNaN(clientId) || clientId < 1) {
            alert('Пожалуйста, введите положительное значение для Client ID.');
            return;
        }

        try {
            const response = await fetch(`/?client_id=${clientId}`);
            const data = await response.json();
            if (response.ok) {
                displayClientData(data);
                document.getElementById('client_id').value = clientId;
            } else {
                alert(data.error);
            }
        } catch (error) {
            alert('Произошла ошибка при получении данных клиента.');
        }
    }

    async function migrate() {
        const response = await fetch(`/?migrate=1`);
        const data = await response.json();
        if (response.ok) {
            alert('Миграции выполнены')
        } else {
            alert(data.error);
        }
    }

    async function flushDatabase() {
        //символически добавлено значение параметра (password), в реальных условиях вывел бы в input[type=password], но в ТЗ не предусмотрено
        const response = await fetch(`/?flush_database=password`);
        const data = await response.json();
        if (response.ok) {
            alert('База очищена')
        } else {
            alert(data.error);
        }
    }

    async function addProducts() {
        const productsJson = document.getElementById('products_json').value;
        try {
            const response = await fetch('/', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({products: JSON.parse(productsJson)})
            });
            const data = await response.json();
            if (response.ok) {
                alert('Продукты успешно добавлены:' + JSON.stringify(data));
            } else {
                alert(data.error);
            }
        } catch (error) {
            console.error('Ошибка при отправке запроса:', error);
        }
    }

    //для декодирования значений из базы закодированных (против XSS) htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8');
    function decodeHTMLEntities(text) {
        const txt = document.createElement('textarea');
        txt.innerHTML = text;
        return txt.value;
    }

    function displayClientData(data) {
        const clientName = document.getElementById('client_name');
        const ordersTable = document.getElementById('orders_table');
        clientName.innerText = `${data.client.first_name} ${data.client.second_name}`;

        // Очищаем таблицу
        ordersTable.innerHTML = '';

        // Заполняем
        data.orders.forEach(order => {
            const row = ordersTable.insertRow();
            row.insertCell(0).innerText = decodeHTMLEntities(order.title);
            row.insertCell(1).innerText = order.price;
        });

    }

    fetchClientData();

</script>
</body>
</html>
