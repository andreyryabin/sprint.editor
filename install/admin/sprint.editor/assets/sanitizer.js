/**
 * Sanitizer which filters a set of whitelisted tags, attributes and css.
 * For now, the whitelist is small but can be easily extended.
 *
  * @param tags map of allowed tag-attribute-attribute-parsers.
 * @param css array of allowed css elements.
 * @param urls array of allowed url scheme
 */
function HtmlWhitelistedSanitizer(tags, css, urls) {
    this.allowedTags = tags;
    this.allowedCss = css;

    if (urls == null) {
        urls = ['http://', 'https://'];
    }

    if (this.allowedTags == null) {
        // Configure small set of default tags
        var unconstrainted = function(x) { return x; };
        var globalAttributes = {
            'dir': unconstrainted,
            'lang': unconstrainted,
            'title': unconstrainted
        };
        var url_sanitizer = HtmlWhitelistedSanitizer.makeUrlSanitizer(urls);
        this.allowedTags = {
            'a': HtmlWhitelistedSanitizer.mergeMap(globalAttributes, {
                'href': url_sanitizer,
                'title': unconstrainted,
                'target': unconstrainted
            }),
            'img': HtmlWhitelistedSanitizer.mergeMap(globalAttributes, {
                'alt': unconstrainted,
                'height': unconstrainted,
                'src': url_sanitizer,
                'width': unconstrainted
            }),
            'p': globalAttributes,
            'span': globalAttributes,
            'div': globalAttributes,
            'br': globalAttributes,
            'b': globalAttributes,
            'strong': globalAttributes,
            'i': globalAttributes,
            'em': globalAttributes,
            'u': globalAttributes,
            'strike': globalAttributes

        };
    }
    if (this.allowedCss == null) {
        // Small set of default css properties
        this.allowedCss = ['border', 'margin', 'padding'];
    }
}

HtmlWhitelistedSanitizer.makeUrlSanitizer = function(allowed_urls) {
    return function(str) {
        if (!str) { return ''; }
        for (var i in allowed_urls) {
            if (str.startsWith(allowed_urls[i])) {
                return str;
            }
        }
        return '';
    };
};

HtmlWhitelistedSanitizer.mergeMap = function(/*...*/) {
    var r = {};
    for (var arg in arguments) {
        for (var i in arguments[arg]) {
            r[i] = arguments[arg][i];
        }
    }
    return r;
};

HtmlWhitelistedSanitizer.prototype.sanitizeString = function(input) {
    // Use the browser to parse the input. This won't evaluate any potentially
    // dangerous scripts since the element isn't attached to the window's document.
    // To be extra cautious, we could dynamically create an iframe, pass the
    // input to the iframe and get back the sanitized string.
    var doc = document.implementation.createHTMLDocument();
    var div = doc.createElement('div');
    div.innerHTML = input;

    // Return the sanitized version of the node.
    return this.sanitizeNode(div).innerHTML;
};

HtmlWhitelistedSanitizer.prototype.sanitizeNode = function(node) {
    // Note: <form> can have it's nodeName overriden by a child node. It's
    // not a big deal here, so we can punt on this.
    var node_name = node.nodeName.toLowerCase();
    if (node_name == '#text') {
        // text nodes are always safe
        return node;
    }
    if (node_name == '#comment') {
        // always strip comments
        return document.createTextNode('');
    }
    if (!this.allowedTags.hasOwnProperty(node_name)) {
        // escape mode
        // return document.createTextNode(node.outerHTML);
        return document.createTextNode('');
    }

    // create a new node
    var copy = document.createElement(node_name);

    // copy the whitelist of attributes using the per-attribute sanitizer
    for (var n_attr=0; n_attr < node.attributes.length; n_attr++) {
        var attr = node.attributes.item(n_attr).name;
        if (this.allowedTags[node_name].hasOwnProperty(attr)) {
            var sanitizer = this.allowedTags[node_name][attr];
            copy.setAttribute(attr, sanitizer(node.getAttribute(attr)));
        }
    }
    // copy the whitelist of css properties
    for (var css in this.allowedCss) {
        copy.style[this.allowedCss[css]] = node.style[this.allowedCss[css]];
    }

    // recursively sanitize child nodes
    while (node.childNodes.length > 0) {
        var child = node.removeChild(node.childNodes[0]);
        copy.appendChild(this.sanitizeNode(child));
    }
    return copy;
};
