# Person

Person is commonly used for manage each vendor, it can be Supplier, Customer, or our Employee. Because all of them have same entity, so we normalize our table to manage all vendor in this table

## Database

![](_media/erd/persons.png)

### Person Type

Table : `person_type`

Properties :

| Column | Type | Description | Relationship |
| --- | --- | --- | --- |
| id | increment | | |
| code | string | internal code | |
| name | string | type of person | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

Person type is used to categorize each person roles. There is commony used person type in app :

- Supplier
- Customer
- Employee
- Director
- Expeditor

### Person Group

| Column | Type | Description | Relationship |
| --- | --- | --- | --- |
| id | increment | | |
| name | string | category of each person | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

Every person can have group for ex :

- Customer
    - Member
    - Non Member

Each customer in group `member` can have cheaper sale price rather than `non-member`

### Person

| Column | Type | Description | Relationship |
| --- | --- | --- | --- |
| id | increment | | |
| name | string | contact person | |
| email | string | | |
| phone | string | | |
| address | string | | |
| notes | string | | |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| person_type_id | integer | | [person_type.id](/modules/person?id=person-type) |
| person_group_id | integer | | [person_group.id](/modules/person?id=person-group) |

### Relationship

- each person should have 1 person_type, each person_type can have many person
- each person can have 1 person_group, each person_group can have many person



