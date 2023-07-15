async function request(method, uri, body = null) {
    const opts = {
        method,
        headers: { "content-type": "application/json" }
    };

    if (body) opts.body = JSON.stringify(body);

    const res = await fetch(uri, opts);
    return res.json();
}

export const month = {
    async fetchAll(page = 1) {
        return request("GET", `/api/month?page=${page}`);
    },

    async create(name) {
        return request("POST", "/api/month", { name });
    },

    async remove(id) {
        return request("DELETE", `/api/month?id=${id}`);
    },

    async update(id, name) {
        return request("PATCH", `/api/month?id=${id}`, { name });
    }
};

export const day = {
    async update(id, { weight, workout, supplement }) {
        return request("PATCH", `/api/day?id=${id}`, {
            weight,
            workout,
            supplement
        });
    }
};

export async function useApi(fn, loading, error) {
    error.value = null;
    loading.value = true;

    const res = await fn();

    loading.value = false;

    if (res.error) {
        error.value = res.error;
        return null;
    }

    return res.data;
}

