export default new class MovimentacaoService {

    constructor() {
        this.baseUrl = 'v4/api/tributario/issqn/massafalida/movimentacao/';
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

    async get(params) {
        const url = this.baseUrl;

        const req = await axios.get(url, { params });
        const { data: resp } = req;
        
        if (resp.error) {
            throw new Error(resp.message);
        }

        return resp.data;
    }

    async edit(params) {
        const url = this.baseUrl;

        const req = await axios.put(url, { params });
        const { data: resp } = req;
        
        if (resp.error) {
            throw new Error(resp.message);
        }

        return resp.data;
    }

    async delete(params) {
        const url = this.baseUrl;

        const req = await axios.delete(url, { params });
        const { data: resp } = req;
        
        if (resp.error) {
            throw new Error(resp.message);
        }
        return resp.data;
    }

    async getTiposMovs(params) {
        const url = this.baseUrl + 'get-tipos-movs';

        const req = await axios.get(url, { params } );
        const { data: resp } = req;
        
        if (resp.error) {
            throw new Error(resp.message);
        }
        return resp.data;
    }
}