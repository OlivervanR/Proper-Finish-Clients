document.getElementById('searchBar').addEventListener('input', function() {
    let filter = this.value.toUpperCase();
    let clients = document.getElementsByClassName('client');
    for (let i = 0; i < clients.length; i++) {
        let name = clients[i].getElementsByClassName('name')[0];
        if (name.innerHTML.toUpperCase().indexOf(filter) > -1) {
            clients[i].style.display = "";
        } else {
            clients[i].style.display = "none";
        }
    }
});

document.getElementById('filter').addEventListener('change', function() {
    let filter = this.value;
    let clients = Array.from(document.getElementsByClassName('client-link'));
    let clientList = document.getElementById('clientList');

    if (filter === 'name1') {
        clients.sort((a, b) => a.getElementsByClassName('name')[0].textContent.localeCompare(b.getElementsByClassName('name')[0].textContent));
    } else if (filter === 'name2') {
        clients.sort((a, b) => b.getElementsByClassName('name')[0].textContent.localeCompare(a.getElementsByClassName('name')[0].textContent));
    } else if (filter === 'date') {
        clients.sort((a, b) => new Date(a.getElementsByClassName('date')[0].textContent) - new Date(b.getElementsByClassName('date')[0].textContent));
    } else if (filter === 'package1') {
        clients.sort((a, b) => a.getElementsByClassName('package')[0].textContent.localeCompare(b.getElementsByClassName('package')[0].textContent));
    } else if (filter === 'package2') {
        clients.sort((a, b) => b.getElementsByClassName('package')[0].textContent.localeCompare(a.getElementsByClassName('package')[0].textContent));
    }

    clientList.innerHTML = '';
    clients.forEach(client => clientList.appendChild(client));
});