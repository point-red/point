# User & Role

## Database

### Users

**Table Name** `users`

| Column         |   Type    | Description                              | Relationship                             |
| :------------- | :-------: | :--------------------------------------- | ---------------------------------------- |
| id             | increment |                                          |                                          |
| username       |  string   |                                          |                                          |
| password       |  string   | Encrypted using Bcrypt                   |                                          |
| email          |  string   |                                          |                                          |
| remember_token |  string   | Auto generated token to authenticate user |                                          |
| disabled       |  boolean  | Prevent user for login if `true`         |                                          |
| created_at     | timestamp |                                          |                                          |
| updated_at     | timestamp |                                          |                                          |
| created_by     |  integer  | Log user who create this data            | [users.id](/modules/user-and-role?id=users) |
| updated_by     |  integer  | Log user who update this data            | [users.id](/modules/user-and-role?id=users) |

### Role

**Table Name** `roles`

### Permission

**Table Name** `permissions`

## Features

### Create User

| Field Name |   Type   | Label    | Description                              |
| ---------- | :------: | -------- | ---------------------------------------- |
| username   |   text   | Username | username for authenticate user           |
| password   |   text   | Password | password for authenticate user           |
| email      |   text   | Email    | email for authenticate user and sending email |
| roles      | checkbox | Role     | can assign to multiple role              |

### Update User

| Field Name |  Type  | Label    | Description                              |
| ---------- | :----: | -------- | ---------------------------------------- |
| username   |  text  | Username | username for authenticate user           |
| password   |  text  | Password | password for authenticate user           |
| email      |  text  | Email    | email for authenticate user and sending email |
| disable    | button | Disable  | disable user for login                   |
| activate   | button | Activate | enable user for login                    |

### Login

| Field Name | Type | Label    | Description                    |
| ---------- | :--: | -------- | ------------------------------ |
| username   | text | Username | username for authenticate user |
| password   | text | Password | password for authenticate user |

### Reset Password

When user forgot their password, they can request to reset their password with following step

#### Request Password Reset

| Field Name | Type | Label    | Description                              |
| ---------- | :--: | -------- | ----------------------- |
| email      | text | Email    | email for sending email |

#### Update Password

| Field Name      | Type | Label           | Description          |
| --------------- | :--: | --------------- | -------------------- |
| password        | text | Password        | new password         |
| retype_password | text | Retype Password | confirm new password |
| email           | text | Email           | confirm your email   |
