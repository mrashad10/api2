const { default: axios } = require("axios");

const url = "http://localhost/api2"

test(
  "Example GET",
  async () => {
    result = await axios.get(`${url}/example`)
        .then(res => res)
        .catch(res => res.response)

    expect(result.status).toBe(200)
    expect(result.data).toEqual("GET Method")
  }
);
