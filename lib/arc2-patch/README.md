ARC2 Patch
==========

By Christopher Gutteridge

The ARC2 loader does not follow relative redirects, which many servers use in
their 303 headers. It also confuses some sites by including the port in the
HTTP "Host:" header. I've made a slightly patched version of the
ARC2_Reader.php library which works around this. I suggest you use it if you
want to use the "sameAs" Graphite method to it's full potential. My
added/altered lines are marked with #cjg
