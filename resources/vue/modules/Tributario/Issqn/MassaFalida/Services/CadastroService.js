export default new class CadastroService {

    constructor() {
        this.baseUrl = 'v4/api/tributario/issqn/massafalida/';
    }

    async save(params) {
        const url = this.baseUrl;
        const req = await axios.post(url, params);
        const { data: resp } = req;

        if (resp.error) {
            throw new Error(resp.message);
        }

        return resp.data;
    }

    async edit(params) {
        const url = this.baseUrl + 'edit';
        const req = await axios.put(url, params);
        const { data: resp } = req;

        if (resp.error) {
            throw new Error(resp.message);
        }
    }
}
