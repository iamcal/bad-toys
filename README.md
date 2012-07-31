# bad-toys - capture client-side Javascript errors in the wild

This is a work in progress!

See also:
* http://errorception.com/
* http://blog.pamelafox.org/2011/10/client-side-error-logging.html
* https://gist.github.com/1878283
* http://blog.protonet.info/post/9620971736/exception-notifier-javascript

Rather than going for the post-load async script loading, I've taken the approach of
just inlining the error capturing code completely. By putting the filtering work on 
the server side, we can get the entire code down to under 500 bytes, including 
delaying error processing until after page load.

Just copy and paste the contents of `badtoys.js` into a `&lt;script&gt;` tag _before_
any other scripts on your page. You can use Google's Closure Compile to make it small:

* http://closure-compiler.appspot.com/home
