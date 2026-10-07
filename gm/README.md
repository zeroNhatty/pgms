# power-gm (Power Grid Monitor)

- This is the initial idea for the project [Initial Idea](docs/InitialIdea.md).
- This project will have major reasoning notes on [Reasoning Docs](docs/Philosophy.md)
- This project will have a developmental changelogs on [Changelog](docs/Changelog.md)

`Reading the notes will give you more in depth understanding of the decisions that were made and how the design came to be.`

⚠️The up-to-date branch will always be [dev](https://github.com/zeroNhatty/power-gm/tree/dev)

## Immediate Todo

### FIX: On the node simulator

- [x] You can change a node's status to "being_marinated" with a null assignee (technician).
- [ ] The nodes do not currently fetch data from the database to keep their state updated in real-time. **[50%]**
      Unimplemented Features
- [ ] Clean Up UI **[subjective]**

### Implement: Missing manager features for technician management

- [ ] Overall stats (e.g., "totally fixed" counts, total uptime) **[0%]**
- [x] parent-child node relationships ~~(if the parents are off children are off)~~**[works]**
- [ ] Map visualization **[5%]**
- [ ] A websocket or a timed refersh on updateable data **[30% complete]**
- [ ] Technician Management **[0%]**
