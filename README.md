Easy way to create article rating stars =)

usage:


import rateStar from "./class/rateStar";


let rates = document.body.querySelectorAll('[data-rate]')
rates.length && each(rates, (key, val) => new rateStar(val))


<img src="https://www.nuorder.pl/img/rateStar.png" alt="agency rating star ;)">
