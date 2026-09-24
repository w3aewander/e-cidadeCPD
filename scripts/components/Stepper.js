class Stepper {
    constructor(etapas = []) {
        this.etapas = etapas;
        this.index = 0;
    }

    addEtapa(etapa) {
        this.etapas.push(etapa);
    }

    avancaEtapas() {
        this.index++;
    }

    setEtapaAtual(index) {
        this.index = index;
    }

    montaComponente() {
        const ul = document.createElement('ul');
        ul.setAttribute('class', 'stepper');

        if (empty(this.etapas)) {
            return;
        }
        this.etapas.map((etapa, index) => {
            const li = document.createElement('li');
            if (index < this.index) {
                li.classList.add('active');
            }
            if (etapa.titulo) {
                li.setAttribute('title', etapa.titulo)
            }
            if (etapa.icone) {
                const icon = document.createElement('i');
                icon.setAttribute('class', etapa.icone);
                li.appendChild(icon);
            }

            const descricao = document.createElement('span');
            descricao.innerText = etapa.descricao;
            li.appendChild(descricao);

            ul.appendChild(li);
        });

        return ul.outerHTML;
    }

    show(container) {
        container.innerHTML = this.montaComponente();
    }
}
