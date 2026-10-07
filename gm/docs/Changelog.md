# Changelogs
## Contents
* [Day 1](#day-1-july-1--2026)
* [Day 2](#day-2-july-2--2026)
* [Day 6](#day-6-july-6--2026)
* [Day 7](#day-7-july-7--2026)
* [Day 20](#day-20-july-20--2026)

---

### Day 1 (July 1 / 2026)
<details>
<summary><strong>Only Landing Page UI was implemented</strong></summary>

- Today I implemented a simple landing page for the web app. I included a clear description of the platform's purpose and explicitly specified that it is a theoretical simulation.
- Here are screenshots of the UI implemented today:

  ![part 1 of the landing page](src/landing_page_v1_1.png)
  ![part 2 of the landing page](src/landing_page_v1_2.png)

</details>

---

### Day 2 (July 2 / 2026)
<small>Missed Changelog</small>
<strong>Design Decisions and Login UI</strong>

- A basic route called `/operational` was implemented.
- A basic login page was made.
  ![login page screenshot](src/login_page_v1_1.png)
  ![login page footer](src/login_page_v1_2.png)
- Some design decisions were documented in [philosophy](Philosophy.md) equivalent commit being [b545eba](https://github.com/zeroNhatty/power-gm/commit/b545eba1a0d5da40582a56c7008ace42459f18b8)


---

### Day 6 (July 6 / 2026)
<strong>Login Functionality</strong>
  
  - A working login system using [nuxt-auth-sanctum](https://nuxt.com/modules/nuxt-auth-sanctum)


---
### Day 7 (July 7 / 2026)

**User Session & access**

- System remembers user for 1 hour by storing them in cookies commit [2348964](https://github.com/zeroNhatty/power-gm/commit/2348964e2a0be56bcd35192fc2653d901b4f169e)
- Added a custom header component displayed after login (basic must be updated)
- default impelmentation of power nodes data model
- Simple structure rendering of raw power node data
![preiview](src/dashboard_power_node_raw_data_render.png)


---
### Day 20 (July 20, 2026)

<small>Missed Updates Since Day 8</small>

- There have been many changes since day 8 of the project.

<details>
<summary><strong>Node Simulator GUI</strong></summary>

- A simple User Interface to manipulate node statuses made using [DearImGui](https://github.com/ocornut/imgui), a raylib window manager for DearImGui [rlImGui](https://github.com/raylib-extras/rlImGui), [raylib](https://github.com/raysan5/raylib), [cpp-httplib](https://github.com/yhirose/cpp-httplib) in order to ping the Go server, and nlohmann's [json](https://github.com/nlohmann/json) package to handle JSON headers for HTTP responses and requests.

Source: [power-gm-sensor](https://github.com/zeroNhatty/power-gm-sensor)
</details>

<details>
<summary><strong>Golang Server</strong></summary>

- A lightweight go script embedded in the [power-gm-server](https://github.com/zeroNhatty/power-gm-server) repo which can receive simulated node pings, track active pings, and update node status on the DB through a request sent to the Laravel API endpoint.

Source: [power-gm-server/golang-server-sensor](https://github.com/zeroNhatty/power-gm-server/tree/dev/golang-server-sensor)
</details>

<details>
<summary><strong>Http Endpoints</strong></summary>

- There are multiple functional endpoints as of now, as shown below:

| Endpoint | Server Script Side | Method | Purpose |
| --- | --- | --- | --- |
| `auth:sanctum/user` | Laravel | `middleware/get` | Used to authenticate using default configuration shipped with Laravel Sanctum. |
| `/api/nodes` | Laravel | `get` | Fetches all the nodes registered in the database. |
| `/api/node_relations` | Laravel | `get` | Fetches the parent-child relations of every node from the database. |
| `api/node/update/{powerNode}` | Laravel | `put` | Used by the go server (for now, will be extended later) to update node statuses on the database. |
| `/node_collection` | go | - | Serves all nodes and their data when requested. |  
| `/node_relation_collection` | go | - | Serves all nodes relationship data when requested. |  
| `/ping` | go | `post` | Handles active node pings and starting node pings. |
| `/ping/maintenance` | go | `post` | Handles pings for nodes entering `being_maintained` status. |
</details>

<details>
<summary><strong>Node Status View</strong></summary>

- A default component on the main page that displays database node content including ID, location, and status.

Preview:

![node status page screenshotv1](src/node_status_page.png)
</details>