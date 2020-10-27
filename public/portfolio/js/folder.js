const folders = Array.from(document.querySelectorAll('.folder'));

for (let i = 0; i < folders.length; i++) {
  for (let j = 0; j < folders[i].children.length; j++) {
    console.log(folders[i].children[j].classList.add('invisible'));    
  }
  // Add state of the folder
  folders[i].removed = true;
  // add event listener to the button
  folders[i].parentElement.children[0].addEventListener('click', () => {
    // remove and add folders depending on the folder state!
    if (folders[i].removed) {    
      for (let j = 0; j < folders[i].children.length; j++) {
        folders[i].children[j].classList.remove('invisible');
      }
    } else {
      for (let j = 0; j < folders[i].children.length; j++) {
        folders[i].children[j].classList.add('invisible');
      }
    }
    folders[i].removed = !folders[i].removed;
  });
}