require('./css-supports-polyfill.js');
require('./polyfill.js');

var domtoimage = require('dom-to-image');

if (!window.CSS.supports('backdrop-filter')) {
    // List of all elements with 'backdrop-filter' on page
    var elems = [];

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
                    '-webkit-filter': filterVal,
                    '-moz-filter': filterVal,
                    'filter': filterVal
                })
                node.parentNode.insertBefore(bgNode, node);

                var isFixed = window.getComputedStyle(node).getPropertyValue('position') == 'fixed';
                var elem = {
                    'node': node,
                    'bgNode': bgNode,
                    'originalOpacity': node.style.opacity || 1,
                    'scroll': isFixed
                }
                elems.push(elem);

                window.addEventListener(
                    "scroll",
                    function(event) {
                        if (elem.scroll) {
                            var scroll = this.scrollY
                            var scrollx = this.scrollX
                            bgNode.style.backgroundPosition = (-1 * node.offsetLeft - scrollx) + 'px ' + (-1 * node.offsetTop - scroll) + 'px';
                        }
                    },
                    true
                )
            });
        });

        var updateBg = function() {
            // first, hide all nodes
            elems.forEach(function(elem) {
                elem.node.style.opacity = 0;
                elem.bgNode.style.opacity = 0;
            });
            

            // List of all fixed elements, will be hidden at render time
            var fixed = [];
            document.body.getElementsByTagName("*").forEach(function (elem) {
                if (window.getComputedStyle(elem, null).getPropertyValue('position') == 'fixed') {
                    fixed.push({
                        'node': elem,
                        'originalDisplay': elem.style.display
                    })
                    elem.style.display = 'none';
                }
            });

            // then, convert the dom to an image
            domtoimage.toPng(document.body).then(function (dataUrl) {
                // first, restore fixed elements
                fixed.forEach(function(elem) {
                    elem.node.style.display = elem.originalDisplay;
                });

                // now, restore opacity of each node and set blurred image
                elems.forEach(function(elem) {
                    elem.node.style.opacity = elem.originalOpacity;
                    elem.bgNode.style.opacity = 1;
                    var isFixed = window.getComputedStyle(elem.node).getPropertyValue('position') == 'fixed';
                    elem.scroll = isFixed;
                    Object.assign(elem.bgNode.style, {
                        'position': isFixed ? 'fixed' : 'absolute',
                        'top': elem.node.offsetTop + 'px',
                        'left': elem.node.offsetLeft + 'px',
                        'width': elem.node.offsetWidth + 'px',
                        'height': elem.node.offsetHeight + 'px',
                        'background': 'url(' + dataUrl + ') no-repeat ' + (-1 * elem.node.offsetLeft) + 'px ' + (-1 * elem.node.offsetTop) + 'px',
                    });
                });
            });
        };
    
        updateBg();
        window.addEventListener("resize", updateBg);
    });
}