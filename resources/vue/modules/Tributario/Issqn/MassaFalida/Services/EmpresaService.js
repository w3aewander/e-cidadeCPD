export default new class EmpresaService {

    constructor() {
        this.baseUrl = 'v4/api/tributario/issqn/massafalida/';
    }

    /**
     * Busca empresas
     *
     * @param {object} params
     * @returns {Promise}
     */
    async getEmpresas(params) {
        const url = this.baseUrl + 'empresas';

        const req = await axios.get(url, { params })
        const { data: resp } = req;

        if (resp.error) {
            throw new Error(resp.message);
        }

        return resp.data;
    }

    /**
     * Busca empresas relacionadas a raiz do CNPJ
     *
     * @param {object} params
     * @returns {Promise}
     */
    async getEmpresasRelacionadas(params) {
        const url = this.baseUrl + 'empresas-relacionadas';

        const req = await axios.get(url, { params })
        const { data: resp } = req;

        if (resp.error) {
            throw new Error(resp.message);
        }

        return resp.data;
    }

}
