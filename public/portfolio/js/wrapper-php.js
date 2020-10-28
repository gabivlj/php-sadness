function main() {
  const wrapperElement = document.getElementById('WRAPPER_PHP_CODE');
  if (!wrapperElement) return;
  wrapperElement.remove();
  document.querySelector(`.${wrapperElement.attributes.dst.nodeValue}`).appendChild(wrapperElement);
}

main();