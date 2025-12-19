dev:
	npm run dev
ser:
	php artisan serve
run:
	npm run build
cln:
	php artisan optimize:clear
db:
	php artisan migrate:fresh --seed
docs:
	php artisan l5-swagger:generate
