---
title: API Reference

language_tabs:
- bash
- javascript

includes:

search: true

toc_footers:
- <a href='http://github.com/mpociot/documentarian'>Documentation Powered by Documentarian</a>
---
<!-- START_INFO -->
# Info

Welcome to the generated API reference.
[Get Postman Collection](http://localhost/docs/collection.json)
<!-- END_INFO -->

#UserController

This resource handler user authentication.
<!-- START_6e04aced561e6b6782905e2a838cfa35 -->
## api/userProfile

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/userProfile" \
-H "Accept: application/json" \
    -d "user_id"="7816" \

```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/userProfile",
    "method": "POST",
    "data": {
        "user_id": 7816
},
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/userProfile`

#### Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    user_id | integer |  required  | This field is required.

<!-- END_6e04aced561e6b6782905e2a838cfa35 -->

<!-- START_a7b7539897db9c62ebe68e7fa2bed4e0 -->
## api/updateProfile

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/updateProfile" \
-H "Accept: application/json" \
    -d "user_id"="68" \
    -d "first_name"="magni" \
    -d "last_name"="magni" \
    -d "mobile"="68" \

```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/updateProfile",
    "method": "POST",
    "data": {
        "user_id": 68,
        "first_name": "magni",
        "last_name": "magni",
        "mobile": 68
},
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/updateProfile`

#### Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    user_id | integer |  required  | This field is required.
    first_name | string |  required  | This field is required.
    last_name | string |  required  | This field is required.
    mobile | numeric |  required  | This field is required.

<!-- END_a7b7539897db9c62ebe68e7fa2bed4e0 -->

<!-- START_4247a8b7ca33931628c4e2c802511763 -->
## api/propertyDetail

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/propertyDetail" \
-H "Accept: application/json" \
    -d "property_id"="5" \

```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/propertyDetail",
    "method": "POST",
    "data": {
        "property_id": 5
},
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/propertyDetail`

#### Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    property_id | integer |  required  | This field is required.

<!-- END_4247a8b7ca33931628c4e2c802511763 -->

<!-- START_6f163a89f367bc64c85c1a79a50d98d6 -->
## api/getActivityLog

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/getActivityLog" \
-H "Accept: application/json" \
    -d "user_id"="76" \
    -d "record_per_page"="76" \
    -d "barcode_id"="velit" \
    -d "page"="76" \

```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/getActivityLog",
    "method": "POST",
    "data": {
        "user_id": 76,
        "record_per_page": 76,
        "barcode_id": "velit",
        "page": 76
},
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/getActivityLog`

#### Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    user_id | integer |  required  | This field is required.
    record_per_page | integer |  required  | This field is required.
    barcode_id | string |  optional  | 
    page | integer |  required  | This field is required.

<!-- END_6f163a89f367bc64c85c1a79a50d98d6 -->

<!-- START_0b3ffe84a5d3c229483d882d6e78d965 -->
## api/scanQrcode

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/scanQrcode" \
-H "Accept: application/json" \
    -d "barcode_id"="cumque" \

```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/scanQrcode",
    "method": "POST",
    "data": {
        "barcode_id": "cumque"
},
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/scanQrcode`

#### Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    barcode_id | string |  required  | This field is required.

<!-- END_0b3ffe84a5d3c229483d882d6e78d965 -->

<!-- START_b8db78b82b0eebf4935a540ebca352e7 -->
## api/activateCode

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/activateCode" \
-H "Accept: application/json" \
    -d "address1"="quia" \
    -d "address2"="quia" \
    -d "unit_number"="52" \
    -d "user_id"="52" \
    -d "barcode_id"="quia" \
    -d "longitude"="quia" \
    -d "latitude"="quia" \
    -d "type"="52" \

```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/activateCode",
    "method": "POST",
    "data": {
        "address1": "quia",
        "address2": "quia",
        "unit_number": 52,
        "user_id": 52,
        "barcode_id": "quia",
        "longitude": "quia",
        "latitude": "quia",
        "type": 52
},
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/activateCode`

#### Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    address1 | string |  required  | This field is required.
    address2 | string |  required  | This field is required.
    unit_number | integer |  required  | This field is required.
    user_id | integer |  required  | This field is required.
    barcode_id | string |  required  | This field is required.
    longitude | string |  required  | This field is required.
    latitude | string |  required  | This field is required.
    type | integer |  required  | This field is required.

<!-- END_b8db78b82b0eebf4935a540ebca352e7 -->

<!-- START_8068a9889e9f025753c89e7464207dae -->
## api/createViolation

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/createViolation" \
-H "Accept: application/json" \
    -d "user_id"="685" \
    -d "violation_reason"="685" \
    -d "violation_action"="685" \
    -d "picture"="nemo" \
    -d "barcode_id"="nemo" \

```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/createViolation",
    "method": "POST",
    "data": {
        "user_id": 685,
        "violation_reason": 685,
        "violation_action": 685,
        "picture": "nemo",
        "barcode_id": "nemo"
},
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/createViolation`

#### Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    user_id | integer |  required  | This field is required.
    violation_reason | integer |  required  | This field is required.
    violation_action | integer |  required  | This field is required.
    picture | string |  optional  | Allowed mime types: `jpeg`, `jpg` or `png`
    barcode_id | string |  required  | This field is required.

<!-- END_8068a9889e9f025753c89e7464207dae -->

<!-- START_f41fbd26b2f7224cb48bd4155c38ab74 -->
## api/reportViolation

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/reportViolation" \
-H "Accept: application/json" \
    -d "long"="quia" \
    -d "lat"="quia" \
    -d "barcode_id"="quia" \

```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/reportViolation",
    "method": "POST",
    "data": {
        "long": "quia",
        "lat": "quia",
        "barcode_id": "quia"
},
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/reportViolation`

#### Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    long | string |  required  | This field is required.
    lat | string |  required  | This field is required.
    barcode_id | string |  required  | This field is required.

<!-- END_f41fbd26b2f7224cb48bd4155c38ab74 -->

<!-- START_d97a0240a2d7248530123ae44c50afd9 -->
## api/pickUp

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/pickUp" \
-H "Accept: application/json" \
    -d "barcode_id"="nesciunt" \
    -d "lat"="nesciunt" \
    -d "long"="nesciunt" \
    -d "user_id"="37" \

```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/pickUp",
    "method": "POST",
    "data": {
        "barcode_id": "nesciunt",
        "lat": "nesciunt",
        "long": "nesciunt",
        "user_id": 37
},
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/pickUp`

#### Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    barcode_id | string |  required  | This field is required.
    lat | string |  required  | This field is required.
    long | string |  required  | This field is required.
    user_id | integer |  required  | This field is required.

<!-- END_d97a0240a2d7248530123ae44c50afd9 -->

<!-- START_216c82760ef1d3d5e60c42357bcb4667 -->
## api/pickUpV2

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/pickUpV2" \
-H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/pickUpV2",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/pickUpV2`


<!-- END_216c82760ef1d3d5e60c42357bcb4667 -->

<!-- START_eb08bfce2dc5cdf4989a936cb4572c8e -->
## api/scanRevockBarcode

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/scanRevockBarcode" \
-H "Accept: application/json" \
    -d "barcode_id"="aut" \
    -d "lat"="aut" \
    -d "long"="aut" \
    -d "user_id"="29" \

```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/scanRevockBarcode",
    "method": "POST",
    "data": {
        "barcode_id": "aut",
        "lat": "aut",
        "long": "aut",
        "user_id": 29
},
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/scanRevockBarcode`

#### Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    barcode_id | string |  required  | This field is required.
    lat | string |  required  | This field is required.
    long | string |  required  | This field is required.
    user_id | integer |  required  | This field is required.

<!-- END_eb08bfce2dc5cdf4989a936cb4572c8e -->

<!-- START_551da87187657555319a862580d988df -->
## api/note

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/note" \
-H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/note",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/note`


<!-- END_551da87187657555319a862580d988df -->

<!-- START_692313457900f7a7797f9e3bbe10c5b0 -->
## api/noteReason

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/noteReason" \
-H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/noteReason",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/noteReason`


<!-- END_692313457900f7a7797f9e3bbe10c5b0 -->

<!-- START_dd73fe89d9872ce37d284636141ae526 -->
## api/changePassword

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/changePassword" \
-H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/changePassword",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/changePassword`


<!-- END_dd73fe89d9872ce37d284636141ae526 -->

<!-- START_ce24c707b785f4a938a3f47073c12018 -->
## api/getEmployeschedule

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/getEmployeschedule" \
-H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/getEmployeschedule",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/getEmployeschedule`


<!-- END_ce24c707b785f4a938a3f47073c12018 -->

<!-- START_beb5919144763e2fac0863b31634efdc -->
## api/getEmployescheduleV2

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/getEmployescheduleV2" \
-H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/getEmployescheduleV2",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/getEmployescheduleV2`


<!-- END_beb5919144763e2fac0863b31634efdc -->

<!-- START_1ee2f7276175a1f52eb84bf6261c40d3 -->
## api/workPlanFillterApi

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/workPlanFillterApi" \
-H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/workPlanFillterApi",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/workPlanFillterApi`


<!-- END_1ee2f7276175a1f52eb84bf6261c40d3 -->

<!-- START_8e1debfccd4b4114348524b926060661 -->
## api/workPlanFillterApiV2

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/workPlanFillterApiV2" \
-H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/workPlanFillterApiV2",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/workPlanFillterApiV2`


<!-- END_8e1debfccd4b4114348524b926060661 -->

<!-- START_c0fd1ba0ceda5bf4de9b44834429af34 -->
## api/addNoteSchedule

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/addNoteSchedule" \
-H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/addNoteSchedule",
    "method": "POST",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/addNoteSchedule`


<!-- END_c0fd1ba0ceda5bf4de9b44834429af34 -->

<!-- START_a7aeab9d33f1ece82de4a4b9ddb5659a -->
## api/forgotPassword

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/forgotPassword" \
-H "Accept: application/json" \
    -d "email"="marvin.coby@example.org" \

```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/forgotPassword",
    "method": "POST",
    "data": {
        "email": "marvin.coby@example.org"
},
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/forgotPassword`

#### Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    email | email |  required  | This field is required.

<!-- END_a7aeab9d33f1ece82de4a4b9ddb5659a -->

<!-- START_c3fa189a6c95ca36ad6ac4791a873d23 -->
## api/login

> Example request:

```bash
curl -X POST "http://www.trashscan.local/api/login" \
-H "Accept: application/json" \
    -d "email"="uzieme@example.org" \
    -d "password"="rerum" \
    -d "platform"="rerum" \
    -d "device_token"="rerum" \

```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/login",
    "method": "POST",
    "data": {
        "email": "uzieme@example.org",
        "password": "rerum",
        "platform": "rerum",
        "device_token": "rerum"
},
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```


### HTTP Request
`POST api/login`

#### Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    email | email |  required  | This field is required.
    password | string |  required  | This field is required.
    platform | string |  required  | This field is required.
    device_token | string |  required  | This field is required.

<!-- END_c3fa189a6c95ca36ad6ac4791a873d23 -->

<!-- START_8264bce8cda7e3ae9948da3188b69726 -->
## api/testing

> Example request:

```bash
curl -X GET "http://www.trashscan.local/api/testing" \
-H "Accept: application/json"
```

```javascript
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "http://www.trashscan.local/api/testing",
    "method": "GET",
    "headers": {
        "accept": "application/json"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});
```

> Example response:

```json
null
```

### HTTP Request
`GET api/testing`

`HEAD api/testing`


<!-- END_8264bce8cda7e3ae9948da3188b69726 -->

