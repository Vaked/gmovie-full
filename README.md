Dummy data for MovieController.php
===

```
    private $dummyData = [
        'image' => 'https://images-na.ssl-images-amazon.com/images/I/71PwLE%2BJ3mL._AC_SY679_.jpg',
        'title' => 'Fight Club',
        'year' => '1999',
        'director' => 'David Fincher',
        'cast' => 'Brad Pitt, Edward Norton, Helena Bongam Carter',
        'synopsis' => 'Discontented with his capitalistic lifestyle, a white-collared insomniac forms an underground fight club with Tyler, a careless soap salesman. The project soon spirals down into something sinister.'
    ];
```

Activation Reminder CronJob
===
Execute the following commands on the respected server (Inside gmovie/):
---
```
php bin/console cron:create
```
After which you will be prompted to to enter a name and a command. The command is:
---
```
app:notify-user
```
And finally you will be asked to enter a scheduling code for example:
---
```
0 22 * * *
```
This runs everyday at 10 pm

To start the cronjob:
---
```
php bin/console cron:start
```
This will run in the background.

Activation Reminder CronJob
===
Execute the following commands on the respected server (Inside gmovie/):
---
```
php bin/console cron:create
```
This will run in the background.

Push Queue Cron
===
```
app:push-queue
```
Scheduling code for push-queue
---
```
0 0 * * *
```
Consume job
===
```
app:consume
```
Scheduling code for consume
---
```
*/15 * * * *
```
To start the cronjob:
---
```
php bin/console cron:start
```

Genres correseponding to their ID in the API
===

```
  28: "Action"
  12: "Adventure"
  16: "Animation"
  35: "Comedy"
  80: "Crime"
  99: "Documentary"
  18: "Drama"
  10751: "Family"
  14: "Fantasy"
  36: "History"
  27: "Horror"
  10402: "Music"
  9648: "Mystery"
  10749: "Romance"
  878: "Science Fiction"
  10770: "TV Movie"
  53: "Thriller"
  10752: "War"
```

Install Mailcatcher
===

First update apt's repository list, then install build-essentials (for the make command), and MailCatcher's dependencies (Ruby and SQLite).
```
sudo apt-get update
sudo apt-get install -y build-essential software-properties-common
sudo apt-get install -y libsqlite3-dev ruby1.9.1-dev
```
Install MailCatcher.
```
sudo gem install mailcatcher
```
To start mailcatcher:
```
mailcatcher --http-ip=0.0.0.0
```
Access mailcatcher:
```
http:\\domain.example:1080
```
In Your .env file:
```
MAILER_DSN=smtp://localhost:1025
```
