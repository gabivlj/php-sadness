document.getElementById('submit').addEventListener('click', e => {
  const usernameInput = document.getElementById('username_input');
  const passwordInput = document.getElementById('password_input');
  fetch(`${window.location.protocol}//${window.location.hostname}/db_test`, {
    body: JSON.stringify({
      username: usernameInput.value,
      password: passwordInput.value,
    }),
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
  })
    .then(res => res.json())
    .then(_res => {
      window.location.reload();
    })
    .catch(err => console.error(err));
});
