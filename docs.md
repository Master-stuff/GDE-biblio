# RESTful api documentation

## Books Endpoints
---
**Endpoint :** ```/api/books/``` **method :** GET 
**Params :** None
**Description :** outputs all the books in the database (to be improved cuz it might be too much)
**Output Example:**
```json
[
    {
        "id": 1,
        "owner_id": 2,
        "title": "Zahir",
        "author": "Paulo Coelho",
        "isbn": null,
        "genre": "Philosophical",
        "language": "English",
        "description": null,
        "cover_image": null,
        "created_at": "2025-09-19 19:17:05"
    },
    {
        "id": 2,
        "owner_id": 2,
        "title": "L'alchemiste",
        "author": "Paulo Coelho",
        "isbn": null,
        "genre": "Philosophical",
        "language": "French",
        "description": null,
        "cover_image": null,
        "created_at": "2025-09-19 19:33:53"
    },
    {
        "id": 3,
        "owner_id": 2,
        "title": "Sherlok Holmes",
        "author": "Arthur Conan Doyle",
        "isbn": null,
        "genre": "Policier",
        "language": "French",
        "description": null,
        "cover_image": null,
        "created_at": "2025-09-19 19:35:21"
    }
]
```
---
**Endpoint :** ```/api/books/{BookID}``` **method :** GET 
**Params :** None
**Description :** outputs one book (with the id {BookID})
**Output Example:**
query to endpoint : ```/api/books/3```
```json 
[
    {
        "id": 3,
        "owner_id": 2,
        "title": "Sherlok Holmes",
        "author": "Arthur Conan Doyle",
        "isbn": null,
        "genre": "Policier",
        "language": "French",
        "description": null,
        "cover_image": null,
        "created_at": "2025-09-19 19:35:21"
    }
]
```