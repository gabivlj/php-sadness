const wrapperElement = document.getElementById('WRAPPER_PHP_CODE');
wrapperElement.remove();
// console.log(wrapperElement.attributes.dst.nodeValue)
document.querySelector(`.${wrapperElement.attributes.dst.nodeValue}`).appendChild(wrapperElement);