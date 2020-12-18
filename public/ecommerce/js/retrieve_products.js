const button = document.getElementById('submit');

const previous = document.getElementById('previous');
const next = document.getElementById('next');

let page = 1;
let lastEmpty = false;

button.addEventListener('click', () => {
  retrieveProducts();
});
previous.addEventListener('click', () => {
  page--;
  page = Math.max(1, page);
  retrieveProducts();
});
next.addEventListener('click', () => {
  if (lastEmpty) return;
  page = Math.max(1, page + 1);
  retrieveProducts();
});

let loading = false;
function retrieveProducts() {
  if (loading) return;
  loading = true;
  const root = document.getElementById('products');
  if (root)
    root.innerHTML = `<img class="w-24 ml-10" src="https://herpasa.com/img/throbber.gif"></img>`;
  const uri = `${window.location.protocol}//${window.location.hostname}${window.location.pathname}`;
  let searchParams = new URLSearchParams();
  searchParams.set('page', page);
  const elements = Array.from(document.querySelectorAll('.input'))
    .map(el => [el.name, el.value])
    .filter(([_, val]) => val.trim() !== '');
  elements.forEach(element => {
    if (element[1] === 'null') {
      return;
    }
    searchParams.set(element[0], element[1]);
  });
  const completeUri = `${uri}/json?${searchParams.toString()}`;
  fetch(completeUri)
    .then(res => res.json())
    .then(res => {
      const root = document.getElementById('products');
      const { items } = res;
      lastEmpty = items.length === 0;
      root.innerHTML = items.reduce((prev, now) => {
        return `${prev}${itemHTML(now)}`;
      }, '');
      loading = false;
    });
}

retrieveProducts();

function imageIDToUri(imageID) {
  const uri = `${window.location.protocol}//${window.location.hostname}/public/ecommerce/files/${imageID}.png`;
  return uri;
}

function itemHTML({ price, name, image_id, type, id }) {
  const imageID = image_id;
  const imageURI = imageIDToUri(imageID);
  return `<div class="w-full max-w-sm mx-auto rounded-md shadow-md overflow-hidden">
  <div class="flex items-end justify-end h-56 w-full bg-cover" style="background-image: url('${imageURI}')">
      <a class="p-2 rounded-full bg-blue-600 text-white mx-5 -mb-4 hover:bg-blue-500 focus:outline-none focus:bg-blue-500" href="/shop/${type}/${id}">
          <svg class="h-5 w-5" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
      </a>
  </div>
  <div class="px-5 py-3">
      <h3 class="text-gray-700 uppercase">${name}</h3>
      <span class="text-gray-500 mt-2">$ ${price}</span>
  </div>
</div>`;
}
