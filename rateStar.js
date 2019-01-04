export class rateStar {
  constructor() {
    this.init()
  }

  init() {
    if (!this._setVars()) return;
    this._setEvents();
  }

  _setVars() {
    let _this = this;

    _this._el = document.getElementById('rate');
    if (!this._el || !this._el.children) return false;

    _this._docId = parseFloat(this._el.getAttribute('data-id')) || false;
    if (!this._docId) return false;

    _this._rate = parseFloat(this._el.getAttribute('data-rate')) || 0;

    _this._starTotal = this._el.children.length - 2;

    return true;
  }

  _setEvents() {
    this._setStars(this._rate, this._starTotal);

    if (this._verify()) {
      this._setRate();
      this._cleanStars();
    }

  }

  _setStars(rate = 0, total = 0) {
    const starPercentage = (rate / total) * 100;
    const starPercentageRounded = `${(Math.round(starPercentage / 10) * 10)}%`;

    this._el.firstElementChild.style.width = starPercentageRounded;
    this._el.firstElementChild.setAttribute('data-width', starPercentageRounded);
    this._el.querySelector('span').innerHTML = `${rate}/${total}`;
  }

  _setRate() {
    each(this._el.children, (key, val) => {
      if (key < 1 || key >= 6) return;

      val.addEventListener('mouseenter', () => {
        if (!this._verify()) return;

        val.style.cursor = 'pointer';

        switch (key) {
          case 1:
            this._el.firstElementChild.style.width = '20%';
            break;
          case 2:
            this._el.firstElementChild.style.width = '40%';
            break;
          case 3:
            this._el.firstElementChild.style.width = '60%';
            break;
          case 4:
            this._el.firstElementChild.style.width = '80%';
            break;
          case 5:
            this._el.firstElementChild.style.width = '100%';
            break;
        }
        new DOMParser();
      });

      val.addEventListener('click', (e) => {

        !this._verify() ? e.preventDefault() : sendPost({
          url: 'core.php',
          data: `rate=1&art=${this._docId}&val=${key}`,
          cb: (xhr) => {
            localStorage.setItem(this._docId, key);
            this._setStars(parseFloat(xhr.response), this._starTotal);
          }
        })

      });
    });
  }

  _verify() {
    try {
      if (localStorage.getItem(this._docId)) return false;
      return true;
    } catch (e) {
      return true;
    }
  }

  _cleanStars() {
    this._el.parentNode.parentNode.addEventListener('mouseleave', () => {
      this._el.firstElementChild.style.width = this._el.firstElementChild.getAttribute('data-width');
      !this._verify() ? each(this._el.children, (key, val) => {
        val.style.cursor = '';
      }) : '';
    });
  }
}