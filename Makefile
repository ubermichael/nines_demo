# Silence output slightly
# .SILENT:

DB := nines_demo
PROJECT := nines_demo

include etc/Makefile

## -- Local make file

.PHONY: printvars
printvars:
	@$(foreach V,$(sort $(.VARIABLES)), \
		$(if $(filter-out environment% default automatic, \
		$(origin $V)),$(warning $V=$($V) ($(value $V)))))
