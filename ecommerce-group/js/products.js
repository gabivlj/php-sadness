let currentSearch = '';
// products that won't be affected by sort and filtering
let products = [];
// view products
let currentProducts = [];

const searchMethods = {
  priceAsc: (a, b) => {
    return a.price - b.price;
  },
  priceDesc: (a, b) => {
    return b.price - a.price;
  },
  nameAsc: (a, b) => {
    return a.name.localeCompare(b.name);
  },
  nameDesc: (a, b) => {
    return b.name.localeCompare(a.name);
  },
};

const tags = {
  keyboards: true,
  keycaps: true,
  frames: true,
  headset: true,
  players: true,
  albums: true,
};

const searchForm = document.getElementById('search');
const input = document.getElementById('search_term');
const loading = document.getElementById('loading');
const select = document.getElementById('select');
const tagElements = Array.from(document.querySelectorAll('.tag-filter'));

tagElements.forEach(tag => {
  tag.addEventListener('change', e => {
    tags[e.target.name] = e.target.checked;
    currentProducts = products;
    sortBy(prevSearchOrdering);
    filter();
    updateItems();
  });
});

let prevSearchOrdering = '';

select.addEventListener('change', e => {
  prevSearchOrdering = e.target.value;
  sortBy(prevSearchOrdering);
  updateItems();
});

searchForm.addEventListener('submit', e => {
  e.preventDefault();
  currentSearch = input.value;
  getItems();
});

function filter() {
  currentProducts = currentProducts.filter(element => tags[element.type]);
}

function sortBy(search) {
  currentProducts = currentProducts.sort(searchMethods[search] || (() => 0));
}

function getItems() {
  loading.innerHTML = 'Loading...';
  fetch(
    `${window.location.protocol}//${window.location.hostname}/ecommerce-group/products_json.php?search=${currentSearch}`
  )
    .then(el => el.json())
    .then(object => {
      products = object.items;
      currentProducts = products;
      sortBy(prevSearchOrdering);
      filter();
      updateItems();
      loading.innerHTML = '';
      // console.log(currentProducts);
    })
    .catch(err => console.log(err));
}

getItems();

const l = document.getElementById('list');

function updateItems() {
  l.innerHTML = currentProducts
    .map(({ name, type, price, image_uri, id, web }) =>
      newItem(name, type, price, image_uri, id, web)
    )
    .join('');
}

function newItem(name, type, price, imageURI, id, web) {
  return `<li class="list-group-item">
  <div class="media align-items-lg-center flex-column flex-lg-row p-3">
    <div class="media-body order-2 order-lg-1">
      <h5 class="mt-0 font-weight-bold mb-2"><a href="/ecommerce-group/product_html.php?id=${id}&type=${type}&web=${web}">${name}</a></h5>
      <p class="font-italic text-muted mb-0 small">${type.toUpperCase()}</p>
      <div class="d-flex align-items-center justify-content-between mt-1">
        <h6 class="font-weight-bold my-2">$${price}</h6>
        <ul class="list-inline small">
          <li class="list-inline-item m-0"><i class="fa fa-star text-success"></i></li>
          <li class="list-inline-item m-0"><i class="fa fa-star text-success"></i></li>
          <li class="list-inline-item m-0"><i class="fa fa-star text-success"></i></li>
          <li class="list-inline-item m-0"><i class="fa fa-star text-success"></i></li>
          <li class="list-inline-item m-0"><i class="fa fa-star text-success"></i></li>
        </ul>
      </div>
    </div><img src="${imageURI}" alt="Generic placeholder image" width="200" class="ml-lg-5 order-1 order-lg-2">
  </div>
</li>`;
}
