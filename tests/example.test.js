const { default: axios } = require("axios");

const url = "http://localhost/api2"

test("Example GET", async () => {
  result = await axios
    .get(`${url}/example`)
    .then((res) => res)
    .catch((res) => res.response);

  expect(result.status).toBe(200);
  expect(result.data).toEqual("GET Method");
});

test("Example POST validation error", async () => {
  result = await axios
    .post(`${url}/example`, {})
    .then((res) => res)
    .catch((res) => res.response);

  expect(result.status).toBe(400);
  expect(result.data).toEqual([
    "Email is required",
    "Email is not a valid email address",
  ]);
});

test("Example POST Success", async () => {
  result = await axios
    .post(`${url}/example`, {
      email: "user@localhost.localdomain",
    })
    .then((res) => res)
    .catch((res) => res.response);

  expect(result.status).toBe(201);
  expect(result.data).toEqual({
    email: "user@localhost.localdomain",
  });
});

test("Example PUT Success", async () => {
  result = await axios
    .put(`${url}/example`, {
      email: "user@localhost.localdomain",
    })
    .then((res) => res)
    .catch((res) => res.response);

  expect(result.status).toBe(200);
  expect(result.data).toEqual({
    email: "user@localhost.localdomain",
  });
});

