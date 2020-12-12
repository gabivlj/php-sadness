let err = false;
let loading = false;
const errorLabel = document.getElementById('error_label');
const loadingLabel = document.getElementById('loading_label');
document.getElementById('submit').addEventListener('click', e => {
  if (loading) return;
  err = false;
  errorLabel.classList.add('invisible');
  loadingLabel.classList.remove('invisible');
  loading = true;
  const { path } = e.target.attributes;
  const inputs = Array.from(document.querySelectorAll('.input'));
  const attValues = inputs
    .map(input => [input.attributes.name.value, input.value])
    .reduce((prev, [key, value]) => ({ ...prev, [key]: value }), {});
  fetch(
    `${window.location.protocol}//${window.location.hostname}${path.value}`,
    {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(attValues),
    }
  )
    .then(res => {
      if (res.status !== 200) {
        err = true;
      }
      loading = false;
      return res.json();
    })
    .then(res => {
      if (err) {
        errorLabel.innerHTML = res.message;
        loadingLabel.classList.add('invisible');
        errorLabel.classList.remove('invisible');
      } else {
        window.location.href = res.redirect;
      }
      loading = false;
    })
    .catch(err => {
      errorLabel.innerHTML = `Error doing transaction... Try again later.`;
      errorLabel.classList.remove('invisible');
      loadingLabel.classList.add('invisible');
      loading = false;
    });
});
