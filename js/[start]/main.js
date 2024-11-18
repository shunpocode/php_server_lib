fetch("/?name=Maxim&age=20", {method: "POST"}).then(async data => console.log( await data.json()))


console.log("start");
var h1 = document.querySelector('h1');
var btn1 = document.querySelector('#p');
var btn2 = document.querySelector('#m');
Store.counter.onUpdate((v) => h1.innerText = String(v));
btn1.onclick = () => Store.counter.add();
btn2.onclick = () => Store.counter.min();
