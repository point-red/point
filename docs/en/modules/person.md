# Person

Person is commonly used for manage each vendor, it can be Supplier, Customer, or our Employee. Because all of them have same entity, so we normalize our table to manage all vendor in this table

## Database

![](_media/erd/persons.png)

### Person Category

Table : `person_categories`

Properties :

| Column | Type | Description | Relationship |
| --- | --- | --- | --- |
| id | increment | | |
| code | string | unique | |
| name | string | | |
| created_at | timestamp | | |
| updated_at | timestamp | | &nbsp; |

Person type is used to categorize each person roles. There is commony used person type in app :

- Supplier
- Customer
- Employee
- Director
- Expeditor

### Person Group

Table : `person_groups`

| Column | Type | Description | Relationship |
| --- | --- | --- | --- |
| id | increment | | |
| code | string | unique | |
| name | string | | |
| created_at | timestamp | | |
| updated_at | timestamp | | &nbsp; |

Every person can have group for ex :

- Customer
    - Member
    - Non Member

Each customer in group `member` can have cheaper sale price rather than `non-member`

### Person

Table : `persons`

| Column | Type | Description | Relationship |
| --- | --- | --- | --- |
| id | increment | | |
| code | string | unique | |
| name | string | unique(['name', 'person_category_id']) | |
| email | string | nullable | |
| phone | string | nullable | |
| address | text | nullable | |
| notes | text | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| person_category_id | integer | | [person_categories.id](/en/modules/person?id=person-category) |
| person_groups_id | integer | | [person_groups.id](/en/modules/person?id=person-group) |

Relationship :

- each persons should have 1 person_categories, each person_categories can have many persons
- each persons can have 1 person_groups, each person_groups can have many persons



