(function () {
    class ClickCaptcha extends HTMLElement {
        static get observedAttributes() { return ['img', 'count']; }
        constructor() {
            super();
            this.attachShadow({ mode: 'open' });
        }
        attributeChangedCallback(name, v1, v2) {
            if (v1 === v2 || !this.shadowRoot.firstChild) return;
            this._img.src = this.getAttribute('img');
            this._wrap.querySelectorAll('.dot').forEach((e) => e.remove());
            this._loaded = this._img.naturalWidth > 0;
            this._disabled = false;
        }
        connectedCallback() {
            this.shadowRoot.innerHTML = `
                <style>
                    :host { display: block; width: fit-content; -webkit-user-select: none; user-select: none; }
                    .wrap { position: relative; cursor: crosshair; line-height: 0; border-radius: 8px; overflow: hidden; }
                    img { display: block; max-width: 100%; height: auto; }
                    .dot { position: absolute; width: 22px; height: 22px; border-radius: 50%; background: rgba(0,112,243,.5); color: #fff; font-size: 12px; display: flex; align-items: center; justify-content: center; transform: translate(-50%,-50%); }
                    .btns { display: flex; gap: 16px; justify-content: center; margin-top: 6px; }
                    button { padding: 6px 20px; border: 1px solid #cbd5e1; border-radius: 20px; background: #fff; cursor: pointer; }
                    button:active { transform: scale(.96); }
                </style>
                <div class="wrap"><img src="${this.getAttribute('img')}"></div>
                <div class="btns">
                    <button name="refresh" type="button">刷新</button>
                    <button name="reset" type="button">重选</button>
                </div>
            `;
            this._wrap = this.shadowRoot.querySelector('.wrap');
            this._img = this.shadowRoot.querySelector('img');
            this._refresh = this.shadowRoot.querySelector('[name=refresh]');
            this._reset = this.shadowRoot.querySelector('[name=reset]');
            this._loaded = this._img.naturalWidth > 0;
            this._disabled = false;
            this._img.addEventListener('load', () => this._img.naturalWidth && (this._loaded = true));
            this._wrap.addEventListener('click', (e) => {
                if (this._disabled || !this._loaded) return;
                const dots = Array.from(this._wrap.querySelectorAll('.dot'));
                const imgRect = this._img.getBoundingClientRect();
                const clickX = e.clientX - imgRect.left, clickY = e.clientY - imgRect.top;
                if (clickX < 0 || clickY < 0 || clickX > imgRect.width || clickY > imgRect.height) return;
                const dot = document.createElement('span');
                dot.className = 'dot';
                dot.textContent = dots.length + 1;
                dot.style.left = clickX / imgRect.width * 100 + '%';
                dot.style.top = clickY / imgRect.height * 100 + '%';
                this._wrap.appendChild(dot);
                if (dots.length + 1 == parseInt(this.getAttribute('count'))) {
                    this._disabled = true;
                    this.dispatchEvent(new CustomEvent('submit', {
                        detail: {
                            attempt: Array.from(this._wrap.querySelectorAll('.dot')).map((e) => [Math.round(parseFloat(e.style.left) / 100 * this._img.naturalWidth), Math.round(parseFloat(e.style.top) / 100 * this._img.naturalHeight)])
                        }, bubbles: true, composed: true
                    }));
                }
            });
            this._refresh.addEventListener('click', () => {
                if (this._disabled || !this._loaded) return;
                this.dispatchEvent(new CustomEvent('refresh', { bubbles: true, composed: true }));
                this._wrap.querySelectorAll('.dot').forEach((e) => e.remove());
                this._disabled = false;
            });
            this._reset.addEventListener('click', () => {
                if (this._disabled || !this._loaded) return;
                this._wrap.querySelectorAll('.dot').forEach((e) => e.remove());
                this._disabled = false;
            });
        }
        reset() {
            this._wrap.querySelectorAll('.dot').forEach((e) => e.remove());
            this._disabled = false;
        }
    }
    customElements.define('<element-name>', ClickCaptcha);
})();