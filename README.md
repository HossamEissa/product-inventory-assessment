# Product Inventory Assessment


## Setup Instructions

### 1. Clone the repository

```bash
git clone https://github.com/HossamEissa/product-inventory-assessment
cd product-inventory-assessment
```

---

### 2. Create environment file

```bash
cp .env.example .env
```

---

### 3. Start Docker containers

```bash
docker compose up -d --build
```

---

### 4. Generate application key

```bash
docker exec -it inventory-app php artisan key:generate
```

---

### 5. Run migrations

```bash
docker exec -it inventory-app php artisan migrate 
```

---

## Run Tests

```bash
docker exec -it inventory-app php artisan test 
```

---

## Scaling the Application

```bash
docker compose up -d --scale inventory-app=3
```
---

## API Documentation

Full API documentation is available on Postman:
[View Postman Collection](https://documenter.getpostman.com/view/25142654/2sBXqFMNMu)

---
## Access Application

* baseURL: http://127.0.0.1/api
* PostgreSQL: internal (port 5432)
* Redis: internal (port 6379)

---
## Endpoints
| Method | Endpoint                    | Description         |
|--------|-----------------------------|---------------------|
| GET    | /api/products               | List all products   |
| GET    | /api/products/{id}          | Get single product  |
| POST   | /api/products               | Create product      |
| PUT    | /api/products/{id}          | Update product      |
| DELETE | /api/products/{id}          | Soft delete product |
| POST   | /api/products/{id}/stock    | Adjust stock        |
| GET    | /api/products/low-stock     | Low stock list      |
