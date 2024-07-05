-- Comment
--[[
	The multiline comment heading
--]]
return {
	-- True or false
	boolean = true, -- Truth
	-- An answer
	integer = 42,
	--[[ 
		Minimalistically recognizable PI -
	--]]
	number = 3.14,
	["0123"] = "protected key",
	text = "It's a \"quite\" complex text",
	null = nil,
	list = {"one", "two", 3},
	map = {
		key = "value",
		tree = { leaf = "green", 
			root = "brown"
		}
	}
}
