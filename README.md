The pet project (MVP) by Laravel + VueJs (websocket, broadcasting, api) for learning websocket & sortable (draggable).
Hot reloading was made just for tasks sorting.

## Project setup
```
1. vagrant up
2. vagrant ssh
3. cd code
4. npm install && npm run dev (or yarn install because strange bug happens sometimes)
5. composer install
6. art migrate --seed

7. repeat 1-3 steps in the second terminal
8. art queue:work

9. repeat 1-3 steps in the third terminal
10. art websocket:serve

11. art test (in the first terminal)
12. add trello-copy.my to hosts file
13. change path to your project's folder in the Homestead.yaml
```

## Login into different browsers like:
```
user-1@none.com
test

user-2@none.com
test
```

## Hot reloading works just for tasks sorting
