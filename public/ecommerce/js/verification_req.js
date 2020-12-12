const urlParams = new URLSearchParams(window.location.search);
function handleError() {
  const element = document.getElementById('message');
  element.innerHTML = `Error appeared... Check with the page administrator, this shouldn't happen!`;
}
try {
  fetch(
    `${window.location.protocol}//${window.location.hostname}/sign_up/verification`,
    {
      body: JSON.stringify({
        email: urlParams.get('e'),
        token: urlParams.get('t'),
      }),
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
    }
  )
    .then(res => {
      if (res.status !== 200) {
        throw new Error('Non 200 status');
      }
      return res.json();
    })
    .then(res => {
      console.log(res);
      window.location.href = '/sign_up/login?verified=true';
    })
    .catch(_ => {
      handleError();
    });
} catch (err) {
  handleError();
}
