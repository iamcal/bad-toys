# bad-toys - capture client-side Javascript errors in the wild

## Logging browser errors

For some background:
* http://errorception.com/
* http://blog.pamelafox.org/2011/10/client-side-error-logging.html
* https://gist.github.com/1878283
* http://blog.protonet.info/post/9620971736/exception-notifier-javascript

Errorception is a great tool, but with some downsides. It relies on a third party,
you can't export your logs and while it's fast, it's slower than it needs to be. It
uses an async script load just like Google Analytics, mainly so that they can change
the error processing code without users having to update the embedded JS. This is
not needed if you control all the code yourself.

Rather than going for the post-load async script loading, I've taken the approach of
just inlining the error capturing code completely. By putting the filtering work on 
the server side, we can get the entire code down to under 500 bytes, including 
delaying error processing until after page load.

## Usage

Copy and paste the contents of `badtoys.js` into a `<script>` tag _before_
any other scripts on your page. You can use Google's Closure Compile to make it small:

* http://closure-compiler.appspot.com/home

You will need to modify the storage function to post data to your own collection
endpoint (instead of `/jse`). The script is also set up to submit errors via an
HTTP GET, but values are truncated to 255 characters. To store longer errors (like
large stack traces), comment out `get()` and uncomment `post()`.

`capture.php` gives a simple example that logs the 
information from the browser adding some environment info (time, IP, User Agent) 
and cookie info. This is a good place to implement filtering for events you know 
you wont ever care about (like Facebook or Twitter widgets). You can easily port 
this to your own language or framework.

The webapp inside `www/` is for analysing and browsing the errors. It is a work in
progress.

<a href="https://raw.github.com/iamcal/bad-toys/gh-pages/screenshots/demo_2012-08-10.png"><img src="https://raw.github.com/iamcal/bad-toys/gh-pages/screenshots/demo_2012-08-10.png" width="500" /></a>


## Browser documentation

* Gecko: https://developer.mozilla.org/en/DOM/window.onerror
* Opera: http://dev.opera.com/articles/view/better-error-handling-with-window-onerror/
* W3C: http://www.w3.org/wiki/DOM/window.onerror
* IE: http://msdn.microsoft.com/en-us/library/ie/cc197053(v=vs.85).aspx
