require('./css-supports-polyfill.js');
require('./polyfill.js');

var domtoimage = require('dom-to-image');

if (!window.CSS.supports('backdrop-filter')) {
    // List of all elements with 'backdrop-filter' on page
    var elems = [];

    // List of all fixed elements, will be hidden at render time
    var fixed = [];
    document.body.getElementsByTagName("*").forEach(function (elem) {
        if (window.getComputedStyle(elem, null).getPropertyValue('position') == 'fixed') {
            fixed.push({
                'node': elem,
                'originalOpacity': elem.style.opacity || 1
            })
        }
    });

    console.log(fixed);

    Polyfill({
        declarations: ["backdrop-filter:*"]
    })
    .doMatched(function(rules) {
        // Build list of all elements with backdrop-filter on page
        rules.each(function(rule) {
            var filterVal = rule.getDeclaration()['backdrop-filter'];
            
            document.querySelectorAll(rule.getSelectors()).forEach(function(node) {
                var bgNode = document.createElement('div');
                Object.assign(bgNode.style, {
                    'position': 'absolute',
                    'top': '0',
                    'left': '0',
                    'width': '100%',
                    'height': '100%',
                    'z-index': '-9999',
                    '-webkit-filter': filterVal,
                    '-moz-filter': filterVal,
                    'filter': filterVal
                })
                node.appendChild(bgNode);

                var isFixed = window.getComputedStyle(node).getPropertyValue('position') == 'fixed';
                if (isFixed) {
                    window.addEventListener(
                        "scroll",
                        function(event) {
                            var scroll = this.scrollY
                            var scrollx = this.scrollX
                            var rect = node.getBoundingClientRect()
                            scroll = scroll - rect.top
                            bgNode.style.backgroundPosition = (-1 * node.offsetLeft - scrollx) + 'px ' + (-2 * node.offsetTop - scroll) + 'px';
                        },
                        true
                    )
                }

                elems.push({
                    'node': node,
                    'bgNode': bgNode,
                    'originalOpacity': node.style.opacity || 1
                });
            });
        });

        var updateBg = function() {
            // first, hide all nodes
            elems.forEach(function(elem) {
                elem.node.style.opacity = 0;
            });
            fixed.forEach(function(elem) {
                elem.node.style.opacity = 0;
            });

            // then, convert the dom to an image
            domtoimage.toPng(document.body).then(function (dataUrl) {
                // first, restore fixed elements
                fixed.forEach(function(elem) {
                    elem.node.style.opacity = elem.originalOpacity;
                });

                // now, restore opacity of each node and set blurred image
                elems.forEach(function(elem) {
                    elem.node.style.opacity = elem.originalOpacity;
                    Object.assign(elem.bgNode.style, {
                        'background': 'url(' + dataUrl + ') no-repeat ' + (-1 * elem.node.offsetLeft) + 'px ' + (-1 * elem.node.offsetTop) + 'px',
                    });
                });
            });
        };
    
        updateBg();
        window.addEventListener("resize", updateBg);
    });
}