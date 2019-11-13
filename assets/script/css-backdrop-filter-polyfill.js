require('./css-supports-polyfill.js');
require('./polyfill.js');

var domtoimage = require('dom-to-image');
var toPx = require('to-px');

var getNodeProperty = function(node, style) {
    return window.getComputedStyle(node).getPropertyValue(style);
}

if (!window.CSS.supports('backdrop-filter', 'blur(1px)')) {
    // List of all elements with 'backdrop-filter' on page
    var elems = [];

    Polyfill({
        declarations: ["backdrop-filter:*"]
    })
    .doMatched(function(rules) {
        // Build list of all elements with backdrop-filter on page
        rules.each(function(rule) {
            var filterVal = rule.getDeclaration()['backdrop-filter'];
            var extracted = '0px';
            try {
                extracted = filterVal.match(/blur\((\w+)\)/)[1];
            } catch (e) { };
            
            document.querySelectorAll(rule.getSelectors()).forEach(function(node) {
                var containerNode = document.createElement('div');
                Object.assign(containerNode.style, {
                    'overflow': 'hidden',
                });
                node.parentNode.insertBefore(containerNode, node);

                var bgNode = document.createElement('div');
                Object.assign(bgNode.style, {
                    'width': 'calc(100% + 2 * ' + extracted + ')',
                    'height': 'calc(100% + 2 * ' + extracted + ')',
                    'position': 'absolute',
                    'top': '-' + extracted,
                    'left': '-' + extracted,
                    '-webkit-filter': filterVal,
                    '-moz-filter': filterVal,
                    'filter': filterVal
                })
                containerNode.appendChild(bgNode);

                var isFixed = getNodeProperty(node, 'position') == 'fixed';
                var elem = {
                    'node': node,
                    'containerNode': containerNode,
                    'bgNode': bgNode,
                    'originalOpacity': node.style.opacity,
                    'originalContainerOpacity': containerNode.style.opacity,
                    'scroll': isFixed,
                    'offset': toPx(extracted),
                }
                elems.push(elem);

                window.addEventListener(
                    "scroll",
                    function(event) {
                        if (elem.scroll) {
                            window.requestAnimationFrame(() => {
                                var clientTop  = document.documentElement.clientTop  || document.body.clientTop  || 0;
                                var clientLeft = document.documentElement.clientLeft || document.body.clientLeft || 0;
            
                                var box = elem.node.getBoundingClientRect();
                                var offsetLeft = box.left - clientLeft;
                                var offsetTop  = box.top  - clientTop;

                                var scrollY = -1 * offsetTop  + elem.offset - this.scrollY;
                                var scrollX = -1 * offsetLeft + elem.offset - this.scrollX;
                                bgNode.style.backgroundPosition = scrollX + 'px ' + scrollY + 'px';
                            });
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
                elem.containerNode.style.opacity = 0;
            });

            function renderElem(node) {
                var isFixed = false;
                try {
                    isFixed = getNodeProperty(node, 'position') == 'fixed';
                } catch (e) { }
                return !isFixed;
            }

            // then, convert the dom to an image
            domtoimage.toPng(document.body, {filter: renderElem}).then(function (dataUrl) {
                // now, restore opacity of each node and set blurred image
                elems.forEach(function(elem) {
                    elem.node.style.opacity = elem.originalOpacity;
                    elem.containerNode.style.opacity = elem.originalContainerOpacity;
                    var isFixed = getNodeProperty(elem.node, 'position') == 'fixed';
                    elem.scroll = isFixed;
                    Object.assign(elem.containerNode.style, {
                        'position': isFixed ? 'fixed' : 'absolute',
                        'top': elem.node.offsetTop + 'px',
                        'left': elem.node.offsetLeft + 'px',
                        'width': elem.node.offsetWidth + 'px',
                        'height': elem.node.offsetHeight + 'px',
                        'border-top-left-radius':     getNodeProperty(elem.node, 'border-top-left-radius'),
                        'border-top-right-radius':    getNodeProperty(elem.node, 'border-top-right-radius'),
                        'border-bottom-right-radius': getNodeProperty(elem.node, 'border-bottom-right-radius'),
                        'border-bottom-left-radius':  getNodeProperty(elem.node, 'border-bottom-left-radius'),
                    });
                    
                    var clientTop  = document.documentElement.clientTop  || document.body.clientTop  || 0;
                    var clientLeft = document.documentElement.clientLeft || document.body.clientLeft || 0;

                    var box = elem.node.getBoundingClientRect();
                    var offsetLeft = box.left - clientLeft;
                    var offsetTop  = box.top  - clientTop;

                    var posX = -1 * offsetLeft + elem.offset;
                    var posY = -1 * offsetTop  + elem.offset;
                    Object.assign(elem.bgNode.style, {
                        'background': getNodeProperty(document.body, 'background-color') + ' url(' + dataUrl + ') no-repeat ' + posX + 'px ' + posY + 'px',
                    });
                });
            });
        };
    
        updateBg();
        window.addEventListener("resize", updateBg);
    });
}