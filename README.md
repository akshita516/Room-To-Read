**ROOM TO READ**

It is simple library management system build using HTML and CSS (frontend code), PHP (middle ware), MySQL (database) and Apache (server) wherein students and teachers can create personalised logins. 
They can login into the system, view all the books available. CheckIn and CheckOut books and also access their activity status.

Books are represented using different colors signifying different difficulty levels such as Green, Red, Orange, Blue, Yellow.

These colors represent the following:

**Green**: Emergent,

**Red**: Early,

**Orange**: Progressive,

**Blue**: Fluent,

**Yellow**: Advanced.

Theres also a priviledged login for admins wherein they can issue books to students and teachers (checkIn and checkOut options).
They can access the statistics to the library usuage through the interactive and user friendly dashboard.
They can also Add new books and modify them.
Lastly, they can view the activities of all the students and teachers who accessed the library and borrow books.

The database schema is as follows:
database name: jpMorgan
tables:
user(userId, userName, password, userType)

books(bookId, bookName, author, bookCount, totalBooks, category, color)

checkIn(id, userId, bookId, checkInDate)

checkOut(id, userId, bookId, checkOutDate)
