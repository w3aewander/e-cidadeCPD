export default new class HistoricoService {

    constructor() {
        this.baseUrl = 'v4/api/tributario/issqn/massafalida/movimentacao/';
    }

    async save(params) {
        const url = this.baseUrl + 'save-historico';

        await axios.post(url, params);

        return;
    }

    async get(params) {
        const url = this.baseUrl + 'get-historico';

        const req = await axios.get(url, { params });
        const { data: resp } = req;
        
        if (resp.error) {
            throw new Error(resp.message);
        }

        return resp.data;
    }
}
