# Payment API Routes

### 1. Initialize Payment

**URL:** `/payment/initialize`

**Method:** `POST`

**Data to Send:**
```json
{
  "user_id": "<USER_ID>",
  "aff_id": "<AFFILIATE_ID>",
  "product_id": "<PRODUCT_ID>",
  "amount": "<AMOUNT_IN_KOBO>",
  "email": "<USER_EMAIL>",
  "currency": "<CURRENCY_CODE>"
}
```

### 2. Payment Callback

**URL:** `/payment/callback`

**Method:** `POST`

**Data to Send:**
```json
{
  "reference": "<PAYMENT_REFERENCE>",
  "amount": "<AMOUNT_IN_KOBO>",
  "aff_id": "<AFFILIATE_ID>",
  "user_id": "<USER_ID>",
  "product_id": "<PRODUCT_ID>",
  "email": "<USER_EMAIL>",
  "currency": "<CURRENCY_CODE>"
}


# Vendor and User API Routes

### 1. Get Vendor Sales

**URL:** `/vendor/{id}/transactions`

**Method:** `GET`

**Description:** Returns all sales for the specified vendor (user ID).

**Data to Send:** None

**Response:**
```json
{
  "success": true,
  "message": "transaction successful sales",
  "Sales": [
    {
      "id": 1,
      "user_id": 123,
      "product_id": 456,
      "amount": 10000,
      "created_at": "2024-09-13",
      "updated_at": "2024-09-13"
    }
  ]
}
```

---

### 2. Get Vendor Total Sale Amount

**URL:** `/vendor/{id}/total-sales`

**Method:** `GET`

**Description:** Returns the total sales amount made by the vendor (user ID).

**Data to Send:** None

**Response:**
```json
{
  "success": true,
  "message": "total amount sales made",
  "Total sale": 100000
}
```

---

### 3. Get Vendor Earnings

**URL:** `/vendor/{id}/balance`

**Method:** `GET`

**Description:** Returns the total vendor earnings that are available for withdrawal.

**Data to Send:** None

**Response:**
```json
{
  "success": true,
  "message": "total earnings for withdrawal",
  "Total sale": 45000
}
```

---

### 4. Get Vendor Students (Emails and Names)

**URL:** `/vendor/{id}/students`

**Method:** `GET`

**Description:** Returns the emails and names of students who made purchases from the vendor (user ID).

**Data to Send:** None

**Response:**
```json
{
  "success": true,
  "message": "Students retrieved successfully",
  "Students": {
    "student1@example.com": "John Doe",
    "student2@example.com": "Jane Doe"
  }
}
```

---

### 5. Get Affiliate Earnings

**URL:** `/user/{id}/balance`

**Method:** `GET`

**Description:** Returns the total affiliate earnings for the specified user.

**Data to Send:** None

**Response:**
```json
{
  "success": true,
  "message": "total affiliate earnings",
  "Total sale": 60000
}
```
---

# Product Management API Routes

### 1. Add Product

**URL:** `/product/add-product`

**Method:** `POST`

**Description:** Adds a new product.

**Data to Send (Example):**
```json
{
  "name": "Product Name",
  "vendor_id": 1,
  "price": 10000,
  "description": "A brief description of the product"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Product added successfully!",
  "product": {
    "id": 1,
    "name": "Product Name",
    "vendor_id": 1,
    "price": 10000,
    "description": "A brief description of the product",
    "created_at": "2024-09-13",
    "updated_at": "2024-09-13"
  }
}
```

---

### 2. View Products by Vendor

**URL:** `/product/view-product/{vendor_id}`

**Method:** `GET`

**Description:** Retrieves all products that belong to a specific vendor.

**Data to Send:** None

**Response:**
```json
{
  "message": "Products retrieved successfully!",
  "products": [
    {
      "id": 1,
      "name": "Product 1",
      "vendor_id": 1,
      "price": 10000,
      "description": "Description of Product 1",
      "created_at": "2024-09-13",
      "updated_at": "2024-09-13"
    },
    {
      "id": 2,
      "name": "Product 2",
      "vendor_id": 1,
      "price": 15000,
      "description": "Description of Product 2",
      "created_at": "2024-09-13",
      "updated_at": "2024-09-13"
    }
  ]
}
```

---

### 3. View a Specific Product by Vendor

**URL:** `/product/view-product/{vendor_id}/{product_id}`

**Method:** `GET`

**Description:** Retrieves details of a specific product by the given `vendor_id` and `product_id`.

**Data to Send:** None

**Response:**
```json
{
  "message": "Product retrieved successfully!",
  "product": {
    "id": 1,
    "name": "Product 1",
    "vendor_id": 1,
    "price": 10000,
    "description": "Description of Product 1",
    "created_at": "2024-09-13",
    "updated_at": "2024-09-13"
  }
}
```

---

### 4. Delete Product

**URL:** `/product/delete/{id}`

**Method:** `DELETE`

**Description:** Deletes a product based on the given product ID.

**Data to Send:** None

**Response:**
```json
{
  "success": true,
  "message": "Product deleted successfully!"
}
```

If the product doesn't exist:

**Error Response:**
```json
{
  "message": "Product not found."
}
```

---

### 5. Get Products Based on User Referral Status

**Method:** `GET`

**Description:** Retrieves products based on whether the user has a referral ID.

**Response (User has no referral):**
```json
{
  "products": [
    {
      "id": 1,
      "name": "Product 1",
      "vendor_id": 1,
      "price": 10000
    },
    {
      "id": 2,
      "name": "Product 2",
      "vendor_id": 2,
      "price": 15000
    }
  ]
}
```

**Response (User has a referral ID):**
```json
{
  "products": [
    {
      "id": 3,
      "name": "Product 3",
      "vendor_id": 3,
      "refferal_id": 2,
      "price": 20000
    }
  ]
}
```
