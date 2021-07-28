# Secure Information Storage REST API

### General ###

The API uses JSON data in request bodies and responses

### Project setup

* Add `secure-storage.localhost` to your `/etc/hosts`: `127.0.0.1 secure-storage.localhost`

* Run `make init` to initialize project

* Open in browser: http://secure-storage.localhost:8000/item Should get `Full authentication is required to access this resource.` error, because first you need to make `login` call (see `postman_collection.json` or `SecurityController` for more info).

### Run tests

make tests (all but functional)
make tests-functional (only functional tests)

### API credentials

* User: john
* Password: maxsecure

* User2: jane
* Password: minsecure

### Postman requests collection

You can import all available API calls to Postman using `postman_collection.json` file

### API endpoints ###

All API usage requires authentication and thus a valid user. 

#### LOGIN ####
* path: /login
* method: POST
* parameters: username, password
* request body example:
```
{
    "username": "john",
    "password": "maxsecure"
}
```
* response on success: empty json array

#### LOGOUT ####
* path: /logout
* method: POST
* parameters: none
* response on success: redirect to frontpage

#### CREATE ####
* path: /item
* method POST
* parameters: data (string)
* request body example: 
```
{
	"data": "here be data to be saved"
}
```
* response on success: empty json array

#### READ ####
* shows all items of user
* path: /item
* method: GET
* parameters: none
* response on success: json array with all items of the user
* response body example:
```
[
    {
        "id": 1,
        "data": "new data",
        "created_at": {
            "date": "2021-07-28 14:45:17.000000",
            "timezone_type": 3,
            "timezone": "UTC"
        },
        "updated_at": {
            "date": "2021-07-28 16:14:49.000000",
            "timezone_type": 3,
            "timezone": "UTC"
        }
    },
    {
        "id": 2,
        "data": "more secret",
        "created_at": {
            "date": "2021-07-28 14:45:30.000000",
            "timezone_type": 3,
            "timezone": "UTC"
        },
        "updated_at": {
            "date": "2021-07-28 14:45:30.000000",
            "timezone_type": 3,
            "timezone": "UTC"
        }
    }
]
```

#### UPDATE ####
* updates existing item's data
* path: /item/:id
* method: PUT
* parameters: data (string)
* request body example: 
```
{
	"data": "new updated data for item"
}
```
* response on success: empty json array

#### DELETE ####
* deletes existing item by id
* path: /item/:id
* method: DELETE
* parameters: id (int) in path
* response on success: empty json array


