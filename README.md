# bad-toys - capture client-side Javascript errors in the wild

This is a work in progress! It does not yet log to a server.

See also:
* http://errorception.com/
* http://blog.pamelafox.org/2011/10/client-side-error-logging.html
* https://gist.github.com/1878283
* http://blog.protonet.info/post/9620971736/exception-notifier-javascript

ErrorCeption is a great tool, but with some downsides. It relies on a third party,
you can't export your logs and while it's fast, it's slower than it needs to be. It
uses an async script load just like Google Analytics, mainly so that they can change
the error processing code without users having to update the embedded JS. This is
not needed if you control all the code yourself.

Rather than going for the post-load async script loading, I've taken the approach of
just inlining the error capturing code completely. By putting the filtering work on 
the server side, we can get the entire code down to under 500 bytes, including 
delaying error processing until after page load.

Just copy and paste the contents of `badtoys.js` into a `&lt;script&gt;` tag _before_
any other scripts on your page. You can use Google's Closure Compile to make it small:

* http://closure-compiler.appspot.com/home
