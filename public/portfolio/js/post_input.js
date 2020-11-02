const posts = Array.from(document.querySelectorAll('.postInput'));
const inputFolder = document.getElementById('inputFolder');

posts.forEach(post => {
  post.addEventListener('click', (e) => {
    const { hit, usefile, ...rest} = e.target.attributes;
    const formData  = new FormData();
    const input = document.createElement('input');
    input.type = 'file';
    input.click();
    input.onchange = e => { 
      const file = e.target.files[0]; 
      Object.keys(rest).forEach(key => {
        if (key === 'class') return;
        formData.append(rest[key].nodeName, rest[key].nodeValue);
      });
      formData.append('path', `${inputFolder.value}/`);
      formData.append('file', file);
      fetch(`${window.location.protocol}${window.location.pathname}${hit.nodeValue}`, {
        method: 'POST',
        body: formData,
      }).then(res => {
        if (!res) return;
        return res.json();
      }).then(res => {
        console.log(res)
        window.location.reload();
      }).catch(err => {
        console.error(err);
      });
    }    
  });
});