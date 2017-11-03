# Available Assertion

Laravel provides a variety of custom assertion methods for your [PHPUnit](https://phpunit.de/) tests. These assertions may be accessed on the response that is returned from the `json`, `get`, `post`, `put`, and `delete` test methods:

| Method                                   | Description                              |
| ---------------------------------------- | ---------------------------------------- |
| `$response->assertSuccessful();`         | Assert that the response has a successful status code. |
| `$response->assertStatus($code);`        | Assert that the response has a given code. |
| `$response->assertRedirect($uri);`       | Assert that the response is a redirect to a given URI. |
| `$response->assertHeader($headerName, $value = null);` | Assert that the given header is present on the response. |
| `$response->assertCookie($cookieName, $value = null);` | Assert that the response contains the given cookie. |
| `$response->assertPlainCookie($cookieName, $value = null);` | Assert that the response contains the given cookie (unencrypted). |
| `$response->assertCookieExpired($cookieName);` | Assert that the response contains the given cookie and it is expired. |
| `$response->assertCookieMissing($cookieName);` | Assert that the response does not contains the given cookie. |
| `$response->assertSessionHas($key, $value = null);` | Assert that the session contains the given piece of data. |
| `$response->assertSessionHasErrors(array $keys, $format = null, $errorBag = 'default');` | Assert that the session contains an error for the given field. |
| `$response->assertSessionMissing($key);` | Assert that the session does not contain the given key. |
| `$response->assertJson(array $data);`    | Assert that the response contains the given JSON data. |
| `$response->assertJsonFragment(array $data);` | Assert that the response contains the given JSON fragment. |
| `$response->assertJsonMissing(array $data);` | Assert that the response does not contain the given JSON fragment. |
| `$response->assertExactJson(array $data);` | Assert that the response contains an exact match of the given JSON data. |
| `$response->assertJsonStructure(array $structure);` | Assert that the response has a given JSON structure. |
| `$response->assertViewIs($value);`       | Assert that the given view was returned by the route. |
| `$response->assertViewHas($key, $value = null);` | Assert that the response view was given a piece of data. |
| `$response->assertViewHasAll(array $data);` | Assert that the response view has a given list of data. |
| `$response->assertViewMissing($key);`    | Assert that the response view is missing a piece of bound data. |
| `$response->assertSee($value);`          | Assert that the given string is contained within the response. |
| `$response->assertDontSee($value);`      | Assert that the given string is not contained within the response. |
| `$response->assertSeeText($value);`      | Assert that the given string is contained within the response text. |
| `$response->assertDontSeeText($value);`  | Assert that the given string is not contained within the response text. |
