class rateStar {
  constructor(el) {
    this.init(el)
  }

  init(el) {
    if (!this._setVars(el)) return;
    this._setEvents();
  }

  _setVars(el) {
    this._el = el
    if (!this._el) return false;

    this._docId = parseFloat(this._el.getAttribute('data-id')) || false;
    if (!this._docId) return false;

    this.btn = this._el.parentNode.querySelector('button[data-save]')

    this._rate = parseFloat(this._el.getAttribute('data-rate')) || parseFloat(localStorage.getItem(this._docId)) || 0;
    this._key = 0
    this._starTotal = this._el.children.length - 1;

    return true;
  }

  _setEvents() {
    this._setStars(this._rate, this._starTotal)
    if (!this.btn) return
    this._setRate()
    this.storeData()
    this._cleanStars()
  }

  _setStars(rate = 0, total = 0) {
    const starPercentage = (rate / total) * 100;
    const starPercentageRounded = `${(Math.round(starPercentage / 10) * 10)}%`;

    this._el.children[0].style.width = starPercentageRounded;
    this._el.children[0].setAttribute('data-width', starPercentageRounded);
    let description = document.body.querySelector('data-rate_desc')
    if (description) description.textContent = `${rate}/${total}`;
  }

  _setRate() {
    _each(this._el.children, (key, val) => {
      if (key < 1 || key >= 6) return;

      val.addEventListener('mouseenter', () => {
        val.style.cursor = 'pointer';
        switch (key) {
          case 1:
            this._el.children[0].style.width = '20%';
            break;
          case 2:
            this._el.children[0].style.width = '40%';
            break;
          case 3:
            this._el.children[0].style.width = '60%';
            break;
          case 4:
            this._el.children[0].style.width = '80%';
            break;
          case 5:
            this._el.children[0].style.width = '100%';
            break;
        }
      })

      val.addEventListener('click', (e) => {
        this._rate = key
        this._key = key
        localStorage.setItem(this._docId, key)
        this._setStars(key, this._starTotal)
      })
    })
  }

  _get(name) {
    return this[name]
  }

  storeData() {
    this.btn.addEventListener('click', (e) => {
      e.preventDefault()
      if (!this._get('_key')) return alert('Please select a rating from 1-5')
      e.target.style.display = 'none'
      _render(this._el.parentNode.parentNode, `<p class="rate__plugin--thx">Thank you for your rating.</p>`)
      sendPost({
        url: '/api/rate_add/',
        data: JSON.stringify({art_id: this._docId, set_rate: this._key}),
        cb: (xhr) => {
          let response = JSON.parse(xhr.response)
          localStorage.setItem(this._docId, this._key)
          this._rate = parseFloat(response.rate)
          this._setStars(this._rate, this._starTotal);
        }
      })
    })
  }

  _verify() {
    try {
      return localStorage.getItem(this._docId)
    } catch (e) {
      return true;
    }
  }

  _cleanStars() {
    this._el.parentNode.parentNode.addEventListener('mouseleave', () => {
      this._el.children[0].style.width = this._el.children[0].getAttribute('data-width');
      !this._verify() ? _each(this._el.children, (key, val) => val.style.cursor = '') : '';
    });
  }
}

export default rateStar
