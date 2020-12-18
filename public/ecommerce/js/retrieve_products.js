const button = document.getElementById('submit');

button.addEventListener('click', () => {
  retrieveProducts();
});

let loading = false;
function retrieveProducts() {
  if (loading) return;
  loading = true;
  const uri = `${window.location.protocol}//${window.location.hostname}${window.location.pathname}`;
  let searchParams = new URLSearchParams();
  const elements = Array.from(document.querySelectorAll('.input'))
    .map(el => [el.name, el.value])
    .filter(([_, val]) => val.trim() !== '');
  elements.forEach(element => {
    searchParams.set(element[0], element[1]);
  });
  const completeUri = `${uri}/json?${searchParams.toString()}`;
  fetch(completeUri)
    .then(res => res.json())
    .then(res => {
      console.log(res);
    });
  loading = false;
}

retrieveProducts();
