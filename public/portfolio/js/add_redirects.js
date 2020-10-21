/**
 * @author Gabriel Villalonga
 * @description Will add click listeners to every div/button/etc that has a href tag and the className redirect.
 */

const redirecters = Array.from(document.querySelectorAll('.redirecter'));
redirecters.forEach(el => {
  el.addEventListener('click', (e) => {
    window.location = `${e.target.attributes.href.nodeValue}`;
  });
});
